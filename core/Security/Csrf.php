<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Session;

/**
 * CSRF token uretimi, saklama ve dogrulamayi yonetir.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf.token';

    /**
     * @param Session $session Mevcut token'i saklamak icin kullanilan oturum deposu.
     */
    public function __construct(
        private Session $session
    ) {
    }

    /**
     * Gerekirse olusturarak mevcut CSRF token'ini dondurur.
     *
     * @return string
     */
    public function token(): string
    {
        $token = (string) $this->session->get(self::SESSION_KEY, '');

        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Verilen CSRF token'ini oturum kaydina gore dogrular.
     *
     * @param string|null $token Gonderilen token.
     * @return bool
     */
    public function verify(?string $token): bool
    {
        $current = (string) $this->session->get(self::SESSION_KEY, '');

        if ($current === '' || $token === null || $token === '') {
            return false;
        }

        return hash_equals($current, $token);
    }

    /**
     * Saklanan CSRF token'inin yeniden uretilmesini zorlar.
     *
     * @return string
     */
    public function refresh(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->put(self::SESSION_KEY, $token);

        return $token;
    }
}
