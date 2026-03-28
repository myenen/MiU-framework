<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * Method, girdi, dosya ve header'lar icin degismez HTTP istek sarmalayicisi.
 */
final class Request
{
    private ?array $headers = null;
    private array $attributes = [];

    /**
     * @param string $method HTTP metodu.
     * @param string $path Normalize edilmis istek yolu.
     * @param array $query Query string verisi.
     * @param array $post Form verisi.
     * @param array $files Yuklenen dosyalar.
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $post = [],
        private readonly array $files = []
    ) {
    }

    /**
     * PHP superglobal'lerinden bir Request nesnesi olusturur.
     *
     * @return self
     */
    public static function capture(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $post = $_POST;
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');

        if (str_contains(strtolower($contentType), 'application/json')) {
            $rawBody = file_get_contents('php://input');
            $decoded = json_decode((string) $rawBody, true);

            if (is_array($decoded)) {
                $post = $decoded;
            }
        }

        return new self(
            $method,
            $path ?: '/',
            $_GET,
            $post,
            $_FILES
        );
    }

    /**
     * HTTP metodunu dondurur.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Normalize edilmis istek yolunu dondurur.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Bir query string degerini getirir.
     *
     * @param string $key Query anahtari.
     * @param mixed $default Bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Tum query string verisini dondurur.
     *
     * @return array
     */
    public function queryAll(): array
    {
        return $this->query;
    }

    /**
     * Bir POST degerini getirir.
     *
     * @param string $key Girdi anahtari.
     * @param mixed $default Bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Tum POST verisini dondurur.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->post;
    }

    /**
     * Alan adina gore yuklenen dosya metadatasini dondurur.
     *
     * @param string $key Dosya input adi.
     * @return array|null
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    /**
     * Tum yuklenen dosya verisini dondurur.
     *
     * @return array
     */
    public function filesAll(): array
    {
        return $this->files;
    }

    /**
     * Bir HTTP header degerini okur.
     *
     * @param string $key Header adi.
     * @param mixed $default Bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $headers = $this->headers();
        $normalizedKey = strtoupper(str_replace('-', '_', $key));

        return $headers[$normalizedKey] ?? $default;
    }

    /**
     * Verilen header'dan bearer token ya da ham token degerini cikarir.
     *
     * @param string $headerName Incelenecek header.
     * @return string|null
     */
    public function bearerToken(string $headerName = 'Authorization'): ?string
    {
        $header = (string) $this->header($headerName, '');

        if ($header === '') {
            return null;
        }

        if (str_starts_with(strtolower($header), 'bearer ')) {
            return trim(substr($header, 7));
        }

        return trim($header);
    }

    /**
     * Tum header verisini dondurur.
     *
     * @return array
     */
    public function headersAll(): array
    {
        return $this->headers();
    }

    /**
     * Istek yapan istemcinin IP adresini dondurur.
     *
     * @return string
     */
    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    /**
     * Istemcinin user agent bilgisini dondurur.
     *
     * @return string
     */
    public function userAgent(): string
    {
        return (string) $this->header('User-Agent', '');
    }

    /**
     * Istek omru boyunca kullanilacak ek attribute degeri yazar.
     *
     * @param string $key Attribute anahtari.
     * @param mixed $value Saklanacak deger.
     * @return void
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Daha once kaydedilen bir attribute degerini okur.
     *
     * @param string $key Attribute anahtari.
     * @param mixed $default Bulunmazsa donecek varsayilan deger.
     * @return mixed
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * PHP server degiskenlerini buyuk harfli bir header haritasina donusturur.
     *
     * @return array
     */
    private function headers(): array
    {
        if ($this->headers !== null) {
            return $this->headers;
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
                continue;
            }

            if (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $this->headers = $headers;
    }
}
