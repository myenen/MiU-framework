<?php

declare(strict_types=1);

namespace Core\Cache;

/**
 * Basit dosya tabanli cache surucusu.
 */
final class FileCache implements CacheInterface
{
    /**
     * @param string $cachePath Cache dosyalarinin tutuldugu dizin.
     * @param int $defaultTtl Varsayilan cache suresi saniye cinsindendir.
     */
    public function __construct(
        private readonly string $cachePath,
        private readonly int $defaultTtl = 300
    ) {
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->filePath($key);

        if (! is_file($file)) {
            return $default;
        }

        $payload = unserialize((string) file_get_contents($file));

        if (! is_array($payload) || ($payload['expires_at'] !== null && $payload['expires_at'] < time())) {
            @unlink($file);

            return $default;
        }

        return $payload['value'] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        $expiresAt = $ttl === null ? time() + $this->defaultTtl : time() + $ttl;

        file_put_contents($this->filePath($key), serialize([
            'expires_at' => $expiresAt,
            'value' => $value,
        ]), LOCK_EX);
    }

    /**
     * @inheritDoc
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cached = $this->get($key, '__missing__');

        if ($cached !== '__missing__') {
            return $cached;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function forget(string $key): void
    {
        $file = $this->filePath($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Verilen cache anahtarini saklamak icin kullanilan dosya yolunu cozer.
     *
     * @param string $key Cache anahtari.
     * @return string
     */
    private function filePath(string $key): string
    {
        return $this->cachePath . '/' . sha1($key) . '.cache';
    }
}
