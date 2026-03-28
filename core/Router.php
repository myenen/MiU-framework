<?php

declare(strict_types=1);

namespace Core;

use App\Services\AuthorizationService;
use App\Services\IdentityService;
use Core\ApiController;
use Core\Cache\CacheInterface;
use Core\Http\ApiResponse;
use Core\Http\Request;
use Core\Http\Response;
use Core\RateLimit\RateLimiter;
use Core\Security\Csrf;
use ReflectionMethod;

/**
 * Statik ve dinamik rotalari cozer, sonra eslesen controller aksiyonunu calistirir.
 */
final class Router
{
    /**
     * @param Container $container Controller ve middleware benzeri servisleri cozen servis kapsayicisi.
     * @param array $routes Statik rota tanimlari.
     * @param array $config Dinamik rota yapilandirmasi.
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $routes,
        private readonly array $config = [],
        private readonly ?CacheInterface $cache = null
    ) {
    }

    /**
     * Verilen istegi eslesen statik ya da dinamik rotaya yonlendirir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function dispatch(Request $request): Response
    {
        $apiRateLimitResponse = $this->ensureApiRateLimit($request);

        if ($apiRateLimitResponse !== null) {
            return $apiRateLimitResponse;
        }

        foreach ($this->routes as [$method, $path, $handler]) {
            if ($request->method() !== $method || $request->path() !== $path) {
                continue;
            }

            [$class, $action] = $handler;
            $controller = $this->container->get($class);

            $apiAuthResponse = $this->ensureApiAuth($request, $class, $action);

            if ($apiAuthResponse !== null) {
                return $apiAuthResponse;
            }

            $authorizationResponse = $this->ensureAuthorization($request, $class, $action);

            if ($authorizationResponse !== null) {
                return $authorizationResponse;
            }

            if ($request->method() === 'POST') {
                $csrfResponse = $this->ensureCsrf($request);

                if ($csrfResponse !== null) {
                    return $csrfResponse;
                }
            }

            return $controller->{$action}($request);
        }

        $dynamicResponse = $this->dispatchDynamic($request);

        if ($dynamicResponse !== null) {
            return $dynamicResponse;
        }

        if (str_starts_with($request->path(), '/api/')) {
            return ApiResponse::error('Kaynak bulunamadi.', 404, [], [], [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        return Response::html('<h1>404</h1><p>Page not found.</p>', 404);
    }

    /**
     * Statik rota eslesmediginde dinamik rota cozumunu dener.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response|null
     */
    private function dispatchDynamic(Request $request): ?Response
    {
        if (! (bool) ($this->config['dynamic'] ?? false)) {
            return null;
        }

        if ($request->path() === '/') {
            return null;
        }

        $cachedRoute = $this->cachedDynamicRoute($request);

        if ($cachedRoute !== null) {
            $cachedResponse = $this->dispatchResolvedDynamicRoute($request, $cachedRoute);

            if ($cachedResponse !== null) {
                return $cachedResponse;
            }
        }

        $segments = array_values(array_filter(explode('/', trim($request->path(), '/'))));

        if ($segments === []) {
            return null;
        }

        $area = 'site';
        $namespace = (string) (($this->config['namespaces']['site'] ?? 'App\\Controllers\\Site'));

        if (($segments[0] ?? '') === 'admin') {
            $area = 'admin';
            $namespace = (string) (($this->config['namespaces']['admin'] ?? 'App\\Controllers\\Admin'));
            array_shift($segments);
        } elseif (($segments[0] ?? '') === 'api') {
            $area = 'api';
            $namespace = (string) (($this->config['namespaces']['api'] ?? 'App\\Controllers\\Api'));
            array_shift($segments);

            if (($segments[0] ?? '') === 'v1') {
                array_shift($segments);
            }
        }

        if ($segments === []) {
            return null;
        }

        $classSegment = array_shift($segments);

        if ($classSegment === null || $classSegment === '') {
            return null;
        }

        $class = $namespace . '\\' . $this->segmentToStudly($classSegment);

        if (! class_exists($class)) {
            return null;
        }

        $controller = $this->container->get($class);
        $hasExplicitMethodSegment = isset($segments[0]);
        $methodSegment = $hasExplicitMethodSegment
            ? (string) array_shift($segments)
            : (string) ($this->config['default_method'] ?? 'index');
        $methodCandidates = $this->resolveMethodCandidates($methodSegment, $hasExplicitMethodSegment);

        foreach ($methodCandidates as $method) {
            $reflectionMethod = $this->resolvePublicActionMethod($controller, $method);

            if (! $reflectionMethod instanceof ReflectionMethod) {
                continue;
            }

            if (! $this->canInvokeDynamicAction($reflectionMethod, $segments)) {
                continue;
            }

            if ($request->method() === 'POST') {
                $apiAuthResponse = $this->ensureApiAuth($request, $class, $method);

                if ($apiAuthResponse !== null) {
                    return $apiAuthResponse;
                }

                $authorizationResponse = $this->ensureAuthorization($request, $class, $method);

                if ($authorizationResponse !== null) {
                    return $authorizationResponse;
                }

                $csrfResponse = $this->ensureCsrf($request);

                if ($csrfResponse !== null) {
                    return $csrfResponse;
                }
            }

            if ($request->method() !== 'POST') {
                $apiAuthResponse = $this->ensureApiAuth($request, $class, $method);

                if ($apiAuthResponse !== null) {
                    return $apiAuthResponse;
                }

                $authorizationResponse = $this->ensureAuthorization($request, $class, $method);

                if ($authorizationResponse !== null) {
                    return $authorizationResponse;
                }
            }

            $resolved = [
                'class' => $class,
                'method' => $method,
                'params' => $segments,
            ];

            $this->rememberDynamicRoute($request, $resolved);

            return $controller->{$method}($request, ...$segments);
        }

        if ($area === 'api') {
            return ApiResponse::error('Dynamic API handler method not found.', 404, [
                'class' => $class,
                'method_segment' => $methodSegment,
                'methods' => $methodCandidates,
            ], [], [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        return null;
    }

    /**
     * Bir yol segmentini StudlyCase bicimine cevirir.
     *
     * @param string $segment URL segment.
     * @return string
     */
    private function segmentToStudly(string $segment): string
    {
        $parts = preg_split('/[^a-zA-Z0-9]+/', $segment) ?: [$segment];
        $parts = array_filter($parts, static fn (string $part): bool => $part !== '');

        if ($parts === []) {
            return 'Index';
        }

        return implode('', array_map(static function (string $part): string {
            return strtoupper(substr($part, 0, 1)) . substr($part, 1);
        }, $parts));
    }

    /**
     * Bir yol segmentini camelCase bicimine cevirir.
     *
     * @param string $segment URL segment.
     * @return string
     */
    private function segmentToCamel(string $segment): string
    {
        $studly = $this->segmentToStudly($segment);

        return lcfirst($studly);
    }

    /**
     * Method segmentinden cagri adaylarini uretir.
     *
     * @param string $methodSegment URL'den cozulmus method bolumu.
     * @param bool $hasExplicitMethodSegment URL'de method segmenti olup olmadigi.
     * @return array<int, string>
     */
    private function resolveMethodCandidates(string $methodSegment, bool $hasExplicitMethodSegment): array
    {
        $candidates = [
            $this->segmentToStudly($methodSegment),
            $this->segmentToCamel($methodSegment),
        ];

        if (! $hasExplicitMethodSegment) {
            $candidates[] = (string) ($this->config['default_method'] ?? 'index');
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Dinamik dispatch icin sadece public ve cagirilabilir action metodunu cozer.
     *
     * @param object $controller Controller nesnesi.
     * @param string $method Method adi.
     * @return ReflectionMethod|null
     */
    private function resolvePublicActionMethod(object $controller, string $method): ?ReflectionMethod
    {
        if (! method_exists($controller, $method)) {
            return null;
        }

        $reflectionMethod = new ReflectionMethod($controller, $method);

        if (! $reflectionMethod->isPublic()) {
            return null;
        }

        return $reflectionMethod;
    }

    /**
     * Cozulmus action'in verilen URL parametreleriyle cagirilip cagrilamayacagini kontrol eder.
     *
     * @param ReflectionMethod $method Reflection ile cozulmus action.
     * @param array<int, string> $segments URL'den gelen ek segmentler.
     * @return bool
     */
    private function canInvokeDynamicAction(ReflectionMethod $method, array $segments): bool
    {
        $providedCount = 1 + count($segments);
        $requiredCount = $method->getNumberOfRequiredParameters();

        if ($providedCount < $requiredCount) {
            return false;
        }

        if (! $method->isVariadic() && $providedCount > $method->getNumberOfParameters()) {
            return false;
        }

        return true;
    }

    /**
     * Cache'te tutulmus dinamik rota cozumunu dondurur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return array<string, mixed>|null
     */
    private function cachedDynamicRoute(Request $request): ?array
    {
        if (! $this->cache instanceof CacheInterface) {
            return null;
        }

        $cached = $this->cache->get($this->dynamicRouteCacheKey($request));

        return is_array($cached) ? $cached : null;
    }

    /**
     * Basarili dinamik rota cozumunu cache'e yazar.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param array<string, mixed> $resolved Cozulmus route bilgisi.
     * @return void
     */
    private function rememberDynamicRoute(Request $request, array $resolved): void
    {
        if (! $this->cache instanceof CacheInterface) {
            return;
        }

        $ttl = (int) ($this->config['dynamic_cache_ttl'] ?? 300);
        $this->cache->put($this->dynamicRouteCacheKey($request), $resolved, max(1, $ttl));
    }

    /**
     * Cache'ten gelen cozulmus route'u gecerliyse calistirir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param array<string, mixed> $resolved Cache'ten gelen route bilgisi.
     * @return Response|null
     */
    private function dispatchResolvedDynamicRoute(Request $request, array $resolved): ?Response
    {
        $class = (string) ($resolved['class'] ?? '');
        $method = (string) ($resolved['method'] ?? '');
        $params = $resolved['params'] ?? [];

        if ($class === '' || $method === '' || ! is_array($params) || ! class_exists($class)) {
            $this->forgetDynamicRoute($request);

            return null;
        }

        $controller = $this->container->get($class);
        $reflectionMethod = $this->resolvePublicActionMethod($controller, $method);

        if (! $reflectionMethod instanceof ReflectionMethod || ! $this->canInvokeDynamicAction($reflectionMethod, $params)) {
            $this->forgetDynamicRoute($request);

            return null;
        }

        $apiAuthResponse = $this->ensureApiAuth($request, $class, $method);

        if ($apiAuthResponse !== null) {
            return $apiAuthResponse;
        }

        $authorizationResponse = $this->ensureAuthorization($request, $class, $method);

        if ($authorizationResponse !== null) {
            return $authorizationResponse;
        }

        if ($request->method() === 'POST') {
            $csrfResponse = $this->ensureCsrf($request);

            if ($csrfResponse !== null) {
                return $csrfResponse;
            }
        }

        return $controller->{$method}($request, ...$params);
    }

    /**
     * Dinamik route cache kaydini siler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return void
     */
    private function forgetDynamicRoute(Request $request): void
    {
        if (! $this->cache instanceof CacheInterface) {
            return;
        }

        $this->cache->forget($this->dynamicRouteCacheKey($request));
    }

    /**
     * Dinamik route cache anahtarini uretir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return string
     */
    private function dynamicRouteCacheKey(Request $request): string
    {
        return 'dynamic-route:' . sha1($request->method() . ':' . $request->path());
    }

    /**
     * POST istekleri icin CSRF korumasi uygular.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response|null
     */
    private function ensureCsrf(Request $request): ?Response
    {
        if (str_starts_with($request->path(), '/api/')) {
            return null;
        }

        /** @var Csrf $csrf */
        $csrf = $this->container->get(Csrf::class);
        $token = (string) $request->input('_token', (string) $request->header('X-CSRF-TOKEN', ''));

        if ($csrf->verify($token)) {
            return null;
        }

        /** @var Session $session */
        $session = $this->container->get(Session::class);
        $session->flash('auth.error', 'Guvenlik dogrulamasi basarisiz. Lutfen tekrar deneyin.');

        $referer = (string) $request->header('Referer', '');
        if ($referer !== '') {
            $parts = parse_url($referer);
            $path = (string) ($parts['path'] ?? '/');

            return Response::redirect($path !== '' ? $path : '/');
        }

        return Response::redirect('/');
    }

    /**
     * API istekleri icin public endpoint listesi disinda token dogrulamasi uygular.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string|null $controllerClass Cozulmus controller sinifi.
     * @param string|null $action Cozulmus action adi.
     * @return Response|null
     */
    private function ensureApiAuth(Request $request, ?string $controllerClass = null, ?string $action = null): ?Response
    {
        if (! str_starts_with($request->path(), '/api/')) {
            return null;
        }

        if ($this->isPublicApiAction($controllerClass, $action)) {
            return null;
        }

        /** @var IdentityService $identity */
        $identity = $this->container->get(IdentityService::class);
        $result = $identity->authenticateApiRequest($request);

        if ($result->isSuccess()) {
            $request->setAttribute('api_auth', $result->data());
            return null;
        }

        return ApiResponse::error($result->message(), $result->status(), $result->data(), [], [
            'path' => $request->path(),
            'method' => $request->method(),
        ]);
    }

    /**
     * API istekleri icin basit rate limit uygular.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response|null
     */
    private function ensureApiRateLimit(Request $request): ?Response
    {
        if (! str_starts_with($request->path(), '/api/')) {
            return null;
        }

        $apiConfig = is_array($this->config['api'] ?? null) ? $this->config['api'] : [];
        $rateLimit = is_array($apiConfig['rate_limit'] ?? null) ? $apiConfig['rate_limit'] : [];

        if (! (bool) ($rateLimit['enabled'] ?? true)) {
            return null;
        }

        /** @var RateLimiter $limiter */
        $limiter = $this->container->get(RateLimiter::class);
        $headerName = (string) ($apiConfig['header'] ?? 'X-Api-Token');
        $token = (string) ($request->bearerToken($headerName) ?? '');
        $subject = $token !== '' ? 'token:' . sha1($token) : 'ip:' . sha1($request->ip());
        $isLoginPath = in_array($request->path(), ['/api/v1/auth/login', '/api/v1/login'], true);
        $maxAttempts = (int) ($isLoginPath ? ($rateLimit['login_max_attempts'] ?? 10) : ($rateLimit['max_attempts'] ?? 120));
        $decaySeconds = (int) ($isLoginPath ? ($rateLimit['login_decay_seconds'] ?? 60) : ($rateLimit['decay_seconds'] ?? 60));
        $state = $limiter->hit('api-rate:' . $subject, max(1, $maxAttempts), max(1, $decaySeconds));

        if ((bool) ($state['allowed'] ?? false)) {
            return null;
        }

        $retryAfter = (int) ($state['retry_after'] ?? $decaySeconds);

        return ApiResponse::error(
            'Cok fazla API istegi gonderdiniz. Lutfen biraz sonra tekrar deneyin.',
            429,
            [
                'retry_after' => $retryAfter,
            ],
            [],
            [
                'path' => $request->path(),
                'method' => $request->method(),
                'rate_limit' => [
                    'limit' => (int) ($state['limit'] ?? $maxAttempts),
                    'remaining' => (int) ($state['remaining'] ?? 0),
                    'reset_at' => (int) ($state['reset_at'] ?? 0),
                ],
            ],
            [
                'Retry-After' => (string) $retryAfter,
            ]
        );
    }

    /**
     * API controller action'inin public olarak isaretli olup olmadigini kontrol eder.
     *
     * @param string|null $controllerClass Controller sinif adi.
     * @param string|null $action Action adi.
     * @return bool
     */
    private function isPublicApiAction(?string $controllerClass, ?string $action): bool
    {
        if ($controllerClass === null || $action === null) {
            return false;
        }

        if (! is_subclass_of($controllerClass, ApiController::class)) {
            return false;
        }

        return $controllerClass::isPublicAction($action);
    }

    /**
     * Controller action icin gerekiyorsa rol/yetki kontrolu yapar.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string|null $controllerClass Controller sinifi.
     * @param string|null $action Action adi.
     * @return Response|null
     */
    private function ensureAuthorization(Request $request, ?string $controllerClass, ?string $action): ?Response
    {
        if ($controllerClass === null || $action === null || ! is_subclass_of($controllerClass, Controller::class)) {
            return null;
        }

        $permissions = $controllerClass::permissionsForAction($action);

        if ($permissions === []) {
            return null;
        }

        /** @var AuthorizationService $authorization */
        $authorization = $this->container->get(AuthorizationService::class);
        $identity = $this->authorizationIdentity($request);

        if ($authorization->identityCan($identity, $permissions)) {
            return null;
        }

        if (str_starts_with($request->path(), '/api/')) {
            return ApiResponse::error('Bu islem icin yetkiniz yok.', 403, [
                'required_permissions' => $permissions,
            ], [], [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        /** @var Session $session */
        $session = $this->container->get(Session::class);
        $session->flash('auth.error', 'Bu islem icin yetkiniz yok.');

        return Response::redirect($this->isAdminPath($request->path()) ? '/admin' : '/');
    }

    /**
     * Yetki kontrolu icin uygun kimlik verisini cozer.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return array<string, mixed>|null
     */
    private function authorizationIdentity(Request $request): ?array
    {
        if (str_starts_with($request->path(), '/api/')) {
            $apiAuth = $request->attribute('api_auth', []);

            if (! is_array($apiAuth)) {
                return null;
            }

            $identity = $apiAuth['identity'] ?? null;

            return is_array($identity) ? $identity : null;
        }

        /** @var Session $session */
        $session = $this->container->get(Session::class);

        if ($this->isAdminPath($request->path())) {
            $identity = $session->get('auth.admin');

            return is_array($identity) ? $identity : null;
        }

        $identity = $session->get('auth.user');

        return is_array($identity) ? $identity : null;
    }

    /**
     * Verilen yolun admin alani icinde olup olmadigini belirtir.
     *
     * @param string $path Istek yolu.
     * @return bool
     */
    private function isAdminPath(string $path): bool
    {
        return $path === '/admin' || str_starts_with($path, '/admin/');
    }
}
