<?php

declare(strict_types=1);

namespace Core\RateLimit;

use Core\Cache\FileCache;

/**
 * Basit sabit pencere mantigiyla rate limit uygular.
 */
final class RateLimiter
{
    /**
     * @param FileCache $cache Rate limit durumunu saklayan cache.
     * @param array<string, mixed> $config Rate limit ayarlari.
     */
    public function __construct(
        private readonly FileCache $cache,
        private readonly array $config = []
    ) {
    }

    /**
     * @param string $key Rate limit anahtari.
     * @param int $maxAttempts Azami istek sayisi.
     * @param int $decaySeconds Pencere suresi.
     * @return array<string, int|bool>
     */
    public function hit(string $key, int $maxAttempts, int $decaySeconds): array
    {
        $now = time();
        $payload = $this->cache->get($key, []);

        if (! is_array($payload)) {
            $payload = [];
        }

        $resetAt = (int) ($payload['reset_at'] ?? 0);
        $count = (int) ($payload['count'] ?? 0);

        if ($resetAt <= $now) {
            $count = 0;
            $resetAt = $now + max(1, $decaySeconds);
        }

        $count++;
        $allowed = $count <= $maxAttempts;

        $this->cache->put($key, [
            'count' => $count,
            'reset_at' => $resetAt,
        ], max(1, $resetAt - $now));

        return [
            'allowed' => $allowed,
            'limit' => $maxAttempts,
            'remaining' => $allowed ? max(0, $maxAttempts - $count) : 0,
            'retry_after' => max(0, $resetAt - $now),
            'reset_at' => $resetAt,
        ];
    }

    /**
     * Verilen anahtara ait rate limit durumunu sifirlar.
     *
     * @param string $key Rate limit anahtari.
     * @return void
     */
    public function clear(string $key): void
    {
        $this->cache->forget($key);
    }
}
