<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Ortak servis sonuc yardimcilari sunan temel servis sinifi.
 */
abstract class BaseService
{
    /**
     * Basarili servis sonucu olusturur.
     *
     * @param string $message Durum mesaji.
     * @param array $data Sonuc verisi.
     * @param int $status Onerilen HTTP durum kodu.
     * @return ServiceResult
     */
    protected function success(string $message, array $data = [], int $status = 200): ServiceResult
    {
        return ServiceResult::success($message, $data, $status);
    }

    /**
     * Hatali servis sonucu olusturur.
     *
     * @param string $message Hata mesaji.
     * @param array $data Ek sonuc verisi.
     * @param int $status Onerilen HTTP durum kodu.
     * @return ServiceResult
     */
    protected function error(string $message, array $data = [], int $status = 422): ServiceResult
    {
        return ServiceResult::error($message, $data, $status);
    }
}
