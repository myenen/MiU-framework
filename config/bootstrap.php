<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Requests\Admin\AdminLoginRequest;
use App\Requests\Site\SiteLoginRequest;
use App\Services\Admin\AdminLoginPageService;
use App\Services\GlobalTools;
use App\Services\FileUploadService;
use App\Services\IdentityService;
use App\Services\Site\SiteLoginPageService;
use App\Services\SystemInfoService;
use App\Services\UserAuthService;
use App\Services\AuthService;
use App\Services\AuthorizationService;
use Core\Application;
use Core\Cache\FileCache;
use Core\Container;
use Core\Database;
use Core\Http\Request;
use Core\Localization\LanguageService;
use Core\Logging\RequestLogger;
use Core\Orm\Models;
use Core\RateLimit\RateLimiter;
use Core\Security\Csrf;
use Core\Session;
use Core\Validation\Validator;
use Core\View\View;

/**
 * App ve Core namespace'leri icin proje autoloader'ini kaydeder.
 *
 * @param string $class Yuklenecek tam nitelikli sinif adi.
 */
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => dirname(__DIR__) . '/app/',
        'Core\\' => dirname(__DIR__) . '/core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});

/**
 * Uygulama kapsayicisini ve calisma zamani servislerini olusturur ve ayarlar.
 *
 * @param string $basePath Proje kok yolu.
 * @return Application
 */
function bootstrapApplication(string $basePath): Application
{
    $config = require $basePath . '/config/app.php';
    $environment = (string) ($config['environment'] ?? 'local');
    $config['debug'] = resolveDebugConfig($config['debug'] ?? [], $environment, $basePath);
    $config['database'] = resolveDatabaseConfig($config['database'] ?? [], (string) ($config['environment'] ?? 'local'));
    $config['app']['url'] = resolveUrlConfig($config['app'] ?? [], $environment);
    $config['assets']['url'] = buildAssetUrl(
        (string) ($config['app']['url'] ?? ''),
        (string) (($config['assets']['path'] ?? '/assets'))
    );
    $routes = require $basePath . '/config/routes.php';
    $request = Request::capture();
    $sharedViewData = [
        'app_url' => (string) ($config['app']['url'] ?? ''),
        'assets_url' => (string) ($config['assets']['url'] ?? ''),
    ];

    $container = new Container();

    $container->singleton('config', static fn () => $config);
    $container->singleton(Request::class, static fn () => $request);
    $container->singleton(Session::class, static fn () => new Session($config['session'] ?? []));
    $container->singleton(Csrf::class, static fn (Container $container) => new Csrf(
        $container->get(Session::class)
    ));
    $container->singleton(Validator::class, static fn () => new Validator());
    $container->singleton(Database::class, static fn () => new Database(
        $config['database'] ?? []
    ));
    $container->singleton(LanguageService::class, static fn (Container $container) => new LanguageService(
        $container->get(Session::class),
        $container->get(Request::class),
        $config['localization'] ?? []
    ));
    $container->singleton(FileCache::class, static fn () => new FileCache(
        $basePath . '/storage/cache',
        (int) ($config['cache']['ttl'] ?? 300)
    ));
    $container->singleton('view.site', static fn (Container $container) => new View(
        $basePath . '/resources/views/site',
        $config['view']['site_layout'] ?? 'layouts/main',
        $sharedViewData + [
            'current_locale' => $container->get(LanguageService::class)->locale(),
            'current_locale_name' => (string) ($container->get(LanguageService::class)->activeLanguage()['name'] ?? ''),
            'site_user_logged_in' => $container->get(UserAuthService::class)->checkUser() ? '1' : '0',
            'site_user_name' => (string) (($container->get(UserAuthService::class)->user()['name'] ?? '')),
            'site_user_email' => (string) (($container->get(UserAuthService::class)->user()['email'] ?? '')),
            'current_permissions' => (array) (($container->get(UserAuthService::class)->user()['permissions'] ?? [])),
        ],
        $container->get(LanguageService::class)
    ));
    $container->singleton('view.admin', static fn (Container $container) => new View(
        $basePath . '/resources/views/admin',
        $config['view']['admin_layout'] ?? 'layouts/main',
        $sharedViewData + [
            'current_locale' => $container->get(LanguageService::class)->locale(),
            'current_locale_name' => (string) ($container->get(LanguageService::class)->activeLanguage()['name'] ?? ''),
            'admin_logged_in' => $container->get(AuthService::class)->checkAdmin() ? '1' : '0',
            'admin_name' => (string) (($container->get(AuthService::class)->admin()['name'] ?? '')),
            'admin_email' => (string) (($container->get(AuthService::class)->admin()['email'] ?? '')),
            'current_permissions' => (array) (($container->get(AuthService::class)->admin()['permissions'] ?? [])),
        ],
        $container->get(LanguageService::class)
    ));
    $container->singleton('view.mail.site', static fn (Container $container) => new View(
        $basePath . '/resources/views/mail/site',
        $config['mail']['site_layout'] ?? 'layouts/main',
        $sharedViewData + [
            'current_locale' => $container->get(LanguageService::class)->locale(),
            'current_locale_name' => (string) ($container->get(LanguageService::class)->activeLanguage()['name'] ?? ''),
        ],
        $container->get(LanguageService::class)
    ));
    $container->singleton('view.mail.admin', static fn (Container $container) => new View(
        $basePath . '/resources/views/mail/admin',
        $config['mail']['admin_layout'] ?? 'layouts/main',
        $sharedViewData + [
            'current_locale' => $container->get(LanguageService::class)->locale(),
            'current_locale_name' => (string) ($container->get(LanguageService::class)->activeLanguage()['name'] ?? ''),
        ],
        $container->get(LanguageService::class)
    ));
    $container->singleton(SystemInfoService::class, static fn () => new SystemInfoService(
        $config['api'] ?? []
    ));
    $container->singleton(IdentityService::class, static fn (Container $container) => new IdentityService(
        $container->get(AuthorizationService::class),
        $config['api'] ?? []
    ));
    $container->singleton(AdminLoginPageService::class, static fn (Container $container) => new AdminLoginPageService(
        $container->get(AuthService::class),
        $container->get(Session::class),
        $container->get(Csrf::class),
        $container->get(AdminLoginRequest::class),
        $container->get(RateLimiter::class),
        $config['security'] ?? []
    ));
    $container->singleton(SiteLoginPageService::class, static fn (Container $container) => new SiteLoginPageService(
        $container->get(UserAuthService::class),
        $container->get(Session::class),
        $container->get(Csrf::class),
        $container->get(SiteLoginRequest::class),
        $container->get(RateLimiter::class),
        $config['security'] ?? []
    ));
    $container->singleton(FileUploadService::class, static fn () => new FileUploadService(
        $basePath . '/public/uploads',
        $config['upload'] ?? []
    ));
    $container->singleton(GlobalTools::class, static fn (Container $container) => new GlobalTools(
        $basePath . '/storage/logs/mail.log',
        $container->get(FileUploadService::class),
        $config['mail'] ?? [],
        $container->get('view.mail.site'),
        $container->get('view.mail.admin')
    ));
    $container->singleton(RequestLogger::class, static fn () => new RequestLogger(
        $config['logging']['request_log'] ?? []
    ));
    $container->singleton(RateLimiter::class, static fn (Container $container) => new RateLimiter(
        $container->get(FileCache::class),
        $config['api']['rate_limit'] ?? []
    ));

    $container->get(Session::class);
    Models::setDb($container->get(Database::class)->pdo());
    Models::setSchema((string) (($config['database']['schema'] ?? 'public')));

    return new Application(
        $container,
        $request,
        $routes,
        array_merge($config['routing'] ?? [], [
            'api' => $config['api'] ?? [],
        ]),
        $config['debug'] ?? [],
        $config['security'] ?? []
    );
}

/**
 * Debug yapilandirmasini ortama gore cozer.
 *
 * @param array $debugConfig Debug ayarlari.
 * @param string $environment Aktif ortam adi.
 * @param string $basePath Proje kok yolu.
 * @return array
 */
function resolveDebugConfig(array $debugConfig, string $environment, string $basePath): array
{
    $debugConfig['enabled'] = (bool) ($debugConfig['enabled'] ?? ($environment === 'local'));
    $debugConfig['log_file'] = (string) ($debugConfig['log_file'] ?? ($basePath . '/storage/logs/app.log'));

    return $debugConfig;
}

/**
 * Aktif veritabani yapilandirma profilini cozer.
 *
 * @param array $databaseConfig Veritabani yapilandirma dizisi.
 * @param string $environment Aktif ortam adi.
 * @return array
 */
function resolveDatabaseConfig(array $databaseConfig, string $environment = 'local'): array
{
    if (! isset($databaseConfig['connections']) || ! is_array($databaseConfig['connections'])) {
        return $databaseConfig;
    }

    $connectionName = (string) ($databaseConfig['active'] ?? $environment);

    return $databaseConfig['connections'][$connectionName]
        ?? $databaseConfig['connections'][$environment]
        ?? $databaseConfig['connections']['local']
        ?? [];
}

/**
 * Aktif ortam icin URL ayarini cozer.
 *
 * @param array $urlConfig URL yapilandirma dizisi.
 * @param string $environment Aktif ortam adi.
 * @return string
 */
function resolveUrlConfig(array $urlConfig, string $environment = 'local'): string
{
    if (isset($urlConfig['url']) && is_string($urlConfig['url']) && $urlConfig['url'] !== '') {
        return $urlConfig['url'];
    }

    if (! isset($urlConfig['urls']) || ! is_array($urlConfig['urls'])) {
        return '';
    }

    $active = (string) ($urlConfig['active'] ?? $environment);

    return (string) (
        $urlConfig['urls'][$active]
        ?? $urlConfig['urls'][$environment]
        ?? $urlConfig['urls']['local']
        ?? ''
    );
}

/**
 * Uygulama URL'i ve asset yolu kullanarak tam asset adresini olusturur.
 *
 * @param string $appUrl Uygulamanin temel URL'i.
 * @param string $assetPath Asset kok yolu.
 * @return string
 */
function buildAssetUrl(string $appUrl, string $assetPath = '/assets'): string
{
    $base = rtrim($appUrl, '/');
    $path = '/' . ltrim($assetPath, '/');

    if ($base === '') {
        return $path;
    }

    return $base . $path;
}
