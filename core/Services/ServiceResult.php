<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Yapilandirilmis basari/hata yanitlari icin standart servis katmani sonuc nesnesi.
 */
final class ServiceResult
{
    /**
     * @param bool $isSuccess Servis cagrisinin basarili olup olmadigini belirtir.
     * @param string $message Insan tarafindan okunabilir durum mesaji.
     * @param array $data Sonuca eklenen veri yuku.
     * @param int $status Onerilen HTTP durum kodu.
     */
    private function __construct(
        private readonly bool $isSuccess,
        private readonly string $message,
        private readonly array $data = [],
        private readonly int $status = 200
    ) {
    }

    /**
     * Basarili bir servis sonucu olusturur.
     *
     * @param string $message Insan tarafindan okunabilir durum mesaji.
     * @param array $data Sonuc verisi.
     * @param int $status Onerilen HTTP durum kodu.
     * @return self
     */
    public static function success(string $message, array $data = [], int $status = 200): self
    {
        return new self(true, $message, $data, $status);
    }

    /**
     * Hatali bir servis sonucu olusturur.
     *
     * @param string $message Insan tarafindan okunabilir hata mesaji.
     * @param array $data Ek hata verisi.
     * @param int $status Onerilen HTTP durum kodu.
     * @return self
     */
    public static function error(string $message, array $data = [], int $status = 422): self
    {
        return new self(false, $message, $data, $status);
    }

    /**
     * Sonucun basariyi temsil edip etmedigini belirtir.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Onerilen HTTP durum kodunu dondurur.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Sonucun insan tarafindan okunabilir mesajini dondurur.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Sonuca ekli veri yukunu dondurur.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Sonucu serilestirilebilir bir diziye cevirir.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
