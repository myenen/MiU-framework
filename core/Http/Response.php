<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * HTML, yonlendirme ve JSON verileri icin yardimcilari olan HTTP yanit nesnesi.
 */
final class Response
{
    /**
     * @param string $body Yanit govde icerigi.
     * @param int $status HTTP durum kodu.
     * @param array $headers Yanit header'lari.
     */
    public function __construct(
        private readonly string $body,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8']
    ) {
    }

    /**
     * HTML yaniti olusturur.
     *
     * @param string $body HTML icerigi.
     * @param int $status HTTP durum kodu.
     * @param array $headers Ek header'lar.
     * @return self
     */
    public static function html(string $body, int $status = 200, array $headers = []): self
    {
        return new self($body, $status, $headers + ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Yonlendirme yaniti olusturur.
     *
     * @param string $location Hedef URL ya da yol.
     * @param int $status Yonlendirme durum kodu.
     * @return self
     */
    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['Location' => $location]);
    }

    /**
     * JSON yaniti olusturur.
     *
     * @param array $data JSON'a cevrilebilir veri.
     * @param int $status HTTP durum kodu.
     * @param array $headers Ek header'lar.
     * @return self
     */
    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        return new self(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            $status,
            $headers + ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    /**
     * Yanit govdesini dondurur.
     *
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * HTTP durum kodunu dondurur.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Yanit header verisini dondurur.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Mevcut yanita ek header'lar ekleyerek yeni bir Response dondurur.
     *
     * @param array<string, string> $headers Eklenecek header'lar.
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        return new self($this->body, $this->status, array_merge($this->headers, $headers));
    }

    /**
     * Yaniti istemciye gonderir.
     */
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body;
    }
}
