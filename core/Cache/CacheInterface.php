<?php

declare(strict_types=1);

namespace Core\Cache;

/**
 * Framework tarafindan kullanilan asgari cache islemlerini tanimlar.
 */
interface CacheInterface
{
    /**
     * Anahtara gore cache degerini getirir.
     *
     * @param string $key Cache arama anahtari.
     * @param mixed $default Cache oge bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Verilen TTL icin degeri cache'e yazar.
     *
     * @param string $key Cache saklama anahtari.
     * @param mixed $value Cache'lenecek deger.
     * @param int|null $ttl Saniye cinsinden yasam suresi. Null ise varsayilan TTL kullanilir.
     */
    public function put(string $key, mixed $value, ?int $ttl = null): void;

    /**
     * Cache'deki degeri dondurur ya da ilk erisimde hesaplayip kaydeder.
     *
     * @param string $key Cache anahtari.
     * @param callable $callback Cache bos oldugunda deger uretecek cagrilabilir.
     * @param int|null $ttl Saniye cinsinden yasam suresi. Null ise varsayilan TTL kullanilir.
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed;

    /**
     * Anahtara gore cache degerini siler.
     *
     * @param string $key Silinecek cache anahtari.
     */
    public function forget(string $key): void;
}
