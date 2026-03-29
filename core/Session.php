<?php

declare(strict_types=1);

namespace Core;

/**
 * Flash mesaj yardimcilariyla PHP oturumlari etrafinda ince bir sarmalayici.
 */
final class Session
{
    /**
     * @param array<string, mixed> $config Session guvenlik ayarlari.
     */
    public function __construct(array $config = [])
    {
        $this->configureSession($config);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Session cookie guvenlik ayarlarini uygular.
     *
     * @param array<string, mixed> $config Session ayarlari.
     * @return void
     */
    private function configureSession(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name = (string) ($config['name'] ?? 'MIUSESSID');
        $lifetime = (int) ($config['lifetime'] ?? 0);
        $path = (string) ($config['path'] ?? '/');
        $domain = (string) ($config['domain'] ?? '');
        $secure = (bool) ($config['secure'] ?? false);
        $httpOnly = (bool) ($config['http_only'] ?? true);
        $sameSite = (string) ($config['same_site'] ?? 'Lax');

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', $httpOnly ? '1' : '0');
        ini_set('session.cookie_secure', $secure ? '1' : '0');
    }

    /**
     * Bir oturum degerini okur.
     *
     * @param string $key Session key.
     * @param mixed $default Bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Bir oturum degerini saklar.
     *
     * @param string $key Session key.
     * @param mixed $value Saklanacak deger.
     */
    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Bir oturum anahtarini siler.
     *
     * @param string $key Session key.
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Tek seferlik okunmak uzere bir deger saklar.
     *
     * @param string $key Flash anahtari.
     * @param mixed $value Flash degeri.
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Flash degerini okur ve siler.
     *
     * @param string $key Flash anahtari.
     * @param mixed $default Bulunmazsa kullanilacak varsayilan deger.
     * @return mixed
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /**
     * PHP oturum id'sini yeniden uretir.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Mevcut oturumu ve cerezini tamamen yok eder.
     */
    public function invalidate(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
