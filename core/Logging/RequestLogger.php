<?php

declare(strict_types=1);

namespace Core\Logging;

use Core\Http\Request;
use Core\Http\Response;
use Core\Orm\Models;
use Throwable;

/**
 * Gelen ve giden HTTP isteklerini veritabanindaki log tablosuna kaydeder.
 */
final class RequestLogger
{
    /**
     * @param array $config Request log davranis ayarlari.
     */
    public function __construct(
        private readonly array $config = []
    ) {
    }

    /**
     * Istek ve yanit bilgisini log tablosuna yazar.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param Response $response Uretilen HTTP yaniti.
     * @param Throwable|null $exception Varsa olusan hata nesnesi.
     * @return void
     */
    public function log(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (! (bool) ($this->config['enabled'] ?? true)) {
            return;
        }

        $timestamp = time();
        $log = Models::get('log');
        $log->method = $request->method();
        $log->path = $request->path();
        $log->query_params = $this->encode($this->maskRecursive($request->queryAll()));
        $log->request_body = $this->encode($this->maskRecursive($request->all()));
        $log->request_headers = $this->encode($this->maskRecursive($request->headersAll()));
        $log->request_files = $this->encode($this->maskRecursive($request->filesAll()));
        $log->response_status = $response->status();
        $log->response_headers = $this->encode($this->maskRecursive($response->headers()));
        $log->response_body = $this->responseBodyForLogging($request, $response);
        $log->ip_address = $request->ip();
        $log->user_agent = $this->limit($request->userAgent(), (int) ($this->config['max_user_agent_length'] ?? 500));
        $log->error_message = $exception?->getMessage() ?? '';
        $log->created_at = $timestamp;
        $log->updated_at = $timestamp;
        $log->save();
    }

    /**
     * Veriyi JSON metnine donusturur.
     *
     * @param array $payload Kaydedilecek veri.
     * @return string
     */
    private function encode(array $payload): string
    {
        return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Metin alanlarini makul boyutta sinirlar.
     *
     * @param string $value Kaydedilecek metin.
     * @param int $length Azami karakter sayisi.
     * @return string
     */
    private function limit(string $value, int $length): string
    {
        if (mb_strlen($value) <= $length) {
            return $value;
        }

        return mb_substr($value, 0, $length);
    }

    /**
     * Hassas anahtarlari maskeleyerek kayit altina alinacak veriyi temizler.
     *
     * @param array $payload Kaydedilecek veri.
     * @return array
     */
    private function maskRecursive(array $payload): array
    {
        $masked = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->maskRecursive($value);
                continue;
            }

            if ($this->shouldMaskKey((string) $key)) {
                $masked[$key] = '[REDACTED]';
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }

    /**
     * Belirli bir anahtarin logda maskelenip maskelenmeyecegini belirtir.
     *
     * @param string $key Veri anahtari.
     * @return bool
     */
    private function shouldMaskKey(string $key): bool
    {
        $normalized = strtolower(str_replace('_', '-', $key));
        $fields = $this->config['mask_fields'] ?? [];

        if (! is_array($fields)) {
            return false;
        }

        return in_array($normalized, array_map(static fn (mixed $field): string => strtolower((string) $field), $fields), true);
    }

    /**
     * Response body kaydini rota bazli kurallarla uretir.
     *
     * @param Request $request Mevcut istek.
     * @param Response $response Uretilen yanit.
     * @return string
     */
    private function responseBodyForLogging(Request $request, Response $response): string
    {
        $skipPaths = $this->config['skip_response_body_paths'] ?? [];

        if (is_array($skipPaths) && in_array($request->path(), $skipPaths, true)) {
            return '[SKIPPED]';
        }

        return $this->limit($response->body(), (int) ($this->config['max_body_length'] ?? 4000));
    }
}
