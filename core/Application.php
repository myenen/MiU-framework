<?php

declare(strict_types=1);

namespace Core;

use Core\Http\ApiResponse;
use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\RequestLogger;
use Core\Cache\FileCache;
use Throwable;

/**
 * Istek yasam dongusunu baslatir ve mevcut istegi router'a devreder.
 */
final class Application
{
    /**
     * @param Container $container Mevcut uygulama icin ortak servis kapsayicisi.
     * @param Request $request Mevcut HTTP istek nesnesi.
     * @param array $routes Statik rota tanimlari.
     * @param array $routerConfig Dinamik router yapilandirmasi.
     * @param array $debugConfig Hata ve debug davranis ayarlari.
     */
    public function __construct(
        private readonly Container $container,
        private readonly Request $request,
        private readonly array $routes,
        private readonly array $routerConfig = [],
        private readonly array $debugConfig = [],
        private readonly array $securityConfig = []
    ) {
    }

    /**
     * Mevcut istegi dagitirir ve uretilen yaniti gonderir.
     */
    public function run(): void
    {
        $response = $this->handle();
        $response->send();
    }

    /**
     * Mevcut istegi isler ve uretilen yaniti dondurur.
     *
     * @return Response
     */
    public function handle(): Response
    {
        $maintenanceResponse = $this->renderMaintenanceResponse();

        if ($maintenanceResponse !== null) {
            return $this->applySecurityHeaders($maintenanceResponse);
        }

        $router = new Router(
            $this->container,
            $this->routes,
            $this->routerConfig,
            $this->container->has(FileCache::class) ? $this->container->get(FileCache::class) : null
        );
        $exception = null;

        try {
            $response = $router->dispatch($this->request);
        } catch (Throwable $throwable) {
            $exception = $throwable;
            $this->logExceptionToFile($throwable);
            $response = $this->renderExceptionResponse($throwable);
        }

        $response = $this->applySecurityHeaders($response);

        if ($this->container->has(RequestLogger::class)) {
            try {
                /** @var RequestLogger $logger */
                $logger = $this->container->get(RequestLogger::class);
                $logger->log($this->request, $response, $exception);
            } catch (Throwable) {
            }
        }

        return $response;
    }

    /**
     * Ortama gore uygun hata yanitini uretir.
     *
     * @param Throwable $throwable Yakalanan hata.
     * @return Response
     */
    private function renderExceptionResponse(Throwable $throwable): Response
    {
        if ($this->isApiRequest()) {
            return ApiResponse::error(
                $this->isDebugEnabled() ? $throwable->getMessage() : 'Beklenmeyen bir hata olustu.',
                500,
                [],
                $this->isDebugEnabled()
                    ? [
                        'type' => $throwable::class,
                        'file' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                    ]
                    : [],
                [
                    'path' => $this->request->path(),
                    'method' => $this->request->method(),
                ]
            );
        }

        if ($this->isDebugEnabled()) {
            return Response::html($this->buildDebugHtml($throwable), 500);
        }

        return Response::html($this->buildProductionHtml(), 500);
    }

    /**
     * Mevcut istegin API istegi olup olmadigini belirtir.
     *
     * @return bool
     */
    private function isApiRequest(): bool
    {
        return str_starts_with($this->request->path(), '/api/');
    }

    /**
     * Debug modunun acik olup olmadigini dondurur.
     *
     * @return bool
     */
    private function isDebugEnabled(): bool
    {
        return (bool) ($this->debugConfig['enabled'] ?? false);
    }

    /**
     * Hatayi dosya tabanli uygulama loguna yazar.
     *
     * @param Throwable $throwable Yakalanan hata.
     * @return void
     */
    private function logExceptionToFile(Throwable $throwable): void
    {
        $logFile = (string) ($this->debugConfig['log_file'] ?? '');

        if ($logFile === '') {
            return;
        }

        $directory = dirname($logFile);

        if (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory)) {
            return;
        }

        $lines = [
            '[' . date('Y-m-d H:i:s') . '] ' . $throwable::class,
            'Path: ' . $this->request->path(),
            'Message: ' . $throwable->getMessage(),
            'File: ' . $throwable->getFile() . ':' . $throwable->getLine(),
            'Trace: ' . $throwable->getTraceAsString(),
            str_repeat('-', 100),
        ];

        @file_put_contents($logFile, implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Local gelistirme ortami icin detayli hata HTML'ini olusturur.
     *
     * @param Throwable $throwable Yakalanan hata.
     * @return string
     */
    private function buildDebugHtml(Throwable $throwable): string
    {
        $title = htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($throwable::class, ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($throwable->getFile(), ENT_QUOTES, 'UTF-8');
        $line = (int) $throwable->getLine();
        $trace = htmlspecialchars($throwable->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        $path = htmlspecialchars($this->request->path(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Exception</title>
    <style>
        body { font-family: Menlo, Monaco, monospace; background:#0f172a; color:#e2e8f0; margin:0; padding:32px; }
        .card { max-width:1100px; margin:0 auto; background:#111827; border:1px solid #334155; border-radius:18px; padding:24px; }
        h1 { margin:0 0 12px; font-size:28px; }
        .meta { color:#94a3b8; margin-bottom:18px; }
        pre { white-space:pre-wrap; word-break:break-word; background:#020617; border:1px solid #1e293b; border-radius:14px; padding:18px; overflow:auto; }
        .label { color:#38bdf8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{$title}</h1>
        <div class="meta">{$type}</div>
        <p><span class="label">Path:</span> {$path}</p>
        <p><span class="label">File:</span> {$file}:{$line}</p>
        <pre>{$trace}</pre>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Production ortami icin sade hata HTML'ini olusturur.
     *
     * @return string
     */
    private function buildProductionHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hata</title>
    <style>
        body { font-family: system-ui, sans-serif; background:#f8fafc; color:#0f172a; margin:0; display:grid; place-items:center; min-height:100vh; padding:24px; }
        .card { max-width:560px; text-align:center; background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:32px; box-shadow:0 16px 50px rgba(15,23,42,.08); }
        h1 { margin:0 0 12px; font-size:28px; }
        p { margin:0; color:#475569; line-height:1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Beklenmeyen bir hata olustu.</h1>
        <p>Islem su anda tamamlanamadi. Lutfen daha sonra tekrar deneyin.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Yapilandirilmis guvenlik header'larini mevcut yanita ekler.
     *
     * @param Response $response Mevcut yanit.
     * @return Response
     */
    private function applySecurityHeaders(Response $response): Response
    {
        $headers = is_array($this->securityConfig['headers'] ?? null) ? $this->securityConfig['headers'] : [];

        if ($headers === []) {
            return $response;
        }

        $normalized = [];

        foreach ($headers as $name => $value) {
            if (! is_string($name) || ! is_scalar($value)) {
                continue;
            }

            $normalized[$name] = (string) $value;
        }

        return $normalized === [] ? $response : $response->withHeaders($normalized);
    }

    /**
     * Bakim modu aktifse uygun yaniti uretir.
     *
     * @return Response|null
     */
    private function renderMaintenanceResponse(): ?Response
    {
        $maintenance = is_array($this->securityConfig['maintenance'] ?? null)
            ? $this->securityConfig['maintenance']
            : [];

        if (! (bool) ($maintenance['enabled'] ?? false)) {
            return null;
        }

        $path = $this->request->path();
        $allowedPaths = array_map('strval', (array) ($maintenance['allowed_paths'] ?? []));
        $allowedIps = array_map('strval', (array) ($maintenance['allowed_ips'] ?? []));

        if (in_array($path, $allowedPaths, true) || in_array($this->request->ip(), $allowedIps, true)) {
            return null;
        }

        $status = max(503, (int) ($maintenance['status'] ?? 503));
        $message = (string) ($maintenance['message'] ?? 'Sistem gecici olarak bakimdadir. Lutfen daha sonra tekrar deneyin.');
        $retryAfter = max(0, (int) ($maintenance['retry_after'] ?? 600));

        if ($this->isApiRequest()) {
            return ApiResponse::error($message, $status, [], [], [
                'path' => $path,
                'method' => $this->request->method(),
                'maintenance' => true,
            ], [
                'Retry-After' => (string) $retryAfter,
            ]);
        }

        $body = $this->buildMaintenanceHtml($message);

        return Response::html($body, $status, [
            'Retry-After' => (string) $retryAfter,
        ]);
    }

    /**
     * Web istekleri icin sade bakim modu sayfasi uretir.
     *
     * @param string $message Kullaniciya gosterilecek metin.
     * @return string
     */
    private function buildMaintenanceHtml(string $message): string
    {
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakim Modu</title>
    <style>
        body { font-family: system-ui, sans-serif; background:#f8fafc; color:#0f172a; margin:0; display:grid; place-items:center; min-height:100vh; padding:24px; }
        .card { max-width:560px; text-align:center; background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:32px; box-shadow:0 16px 50px rgba(15,23,42,.08); }
        h1 { margin:0 0 12px; font-size:28px; }
        p { margin:0; color:#475569; line-height:1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Bakim Modu</h1>
        <p>{$message}</p>
    </div>
</body>
</html>
HTML;
    }
}
