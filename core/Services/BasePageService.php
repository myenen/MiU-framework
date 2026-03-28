<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Session;
use Core\Validation\ValidationResult;

/**
 * Sayfa odakli servisler icin form, flash ve yonlendirme yardimcilari sunar.
 */
abstract class BasePageService extends BaseService
{
    /**
     * Form alanlarini flash oturumuna yazar.
     *
     * @param Session $session Oturum servisi.
     * @param string $prefix Flash anahtar on eki.
     * @param array<string, mixed> $payload Form verisi.
     * @param array<int, string> $fields Saklanacak alanlar.
     * @return void
     */
    protected function flashInput(Session $session, string $prefix, array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            $session->flash($prefix . '.' . $field, (string) ($payload[$field] ?? ''));
        }
    }

    /**
     * Flash oturumundan onceki form degerini okur.
     *
     * @param Session $session Oturum servisi.
     * @param string $prefix Flash anahtar on eki.
     * @param string $field Alan adi.
     * @param string $default Varsayilan deger.
     * @return string
     */
    protected function oldInput(Session $session, string $prefix, string $field, string $default = ''): string
    {
        return (string) $session->getFlash($prefix . '.' . $field, $default);
    }

    /**
     * Flash oturumundaki onceki form degerlerini temizler.
     *
     * @param Session $session Oturum servisi.
     * @param string $prefix Flash anahtar on eki.
     * @param array<int, string> $fields Temizlenecek alanlar.
     * @return void
     */
    protected function clearInput(Session $session, string $prefix, array $fields): void
    {
        foreach ($fields as $field) {
            $session->getFlash($prefix . '.' . $field, '');
        }
    }

    /**
     * Dogrulama hatalarini flash oturumuna yazar.
     *
     * @param Session $session Oturum servisi.
     * @param string $messageKey Genel hata mesaj anahtari.
     * @param string $message Genel hata mesaji.
     * @param ValidationResult $validation Dogrulama sonucu.
     * @param array<string, string> $fieldFlashMap Alan-hata flash anahtari eslesmesi.
     * @return void
     */
    protected function flashValidationErrors(
        Session $session,
        string $messageKey,
        string $message,
        ValidationResult $validation,
        array $fieldFlashMap = []
    ): void {
        $session->flash($messageKey, $message);

        foreach ($fieldFlashMap as $field => $flashKey) {
            $session->flash($flashKey, $validation->first($field));
        }
    }

    /**
     * Yonlendirme bekleyen basarili servis sonucu olusturur.
     *
     * @param string $message Durum mesaji.
     * @param string $redirectTo Hedef yol.
     * @param int $status Onerilen HTTP durum kodu.
     * @param array<string, mixed> $data Ek sonuc verisi.
     * @return ServiceResult
     */
    protected function redirectSuccess(string $message, string $redirectTo, int $status = 302, array $data = []): ServiceResult
    {
        return $this->success($message, array_merge($data, [
            'redirect_to' => $redirectTo,
        ]), $status);
    }

    /**
     * Yonlendirme bekleyen hatali servis sonucu olusturur.
     *
     * @param string $message Hata mesaji.
     * @param string $redirectTo Hedef yol.
     * @param int $status Onerilen HTTP durum kodu.
     * @param array<string, mixed> $data Ek sonuc verisi.
     * @return ServiceResult
     */
    protected function redirectError(string $message, string $redirectTo, int $status = 422, array $data = []): ServiceResult
    {
        return $this->error($message, array_merge($data, [
            'redirect_to' => $redirectTo,
        ]), $status);
    }
}
