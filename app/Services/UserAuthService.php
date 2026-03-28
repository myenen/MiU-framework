<?php

declare(strict_types=1);

namespace App\Services;

use Core\Session;

/**
 * Site kullanicisi oturumunu yoneten ince auth servisi.
 */
final class UserAuthService
{
    /**
     * @param Session $session Kullanici oturumu icin session depolamasi.
     * @param IdentityService $identity Ortak kimlik dogrulama servisi.
     */
    public function __construct(
        private readonly Session $session,
        private readonly IdentityService $identity
    ) {
    }

    /**
     * Kullanici giris denemesini oturum ile birlikte tamamlar.
     *
     * @param string $email E-posta adresi.
     * @param string $password Girilen parola.
     * @return bool
     */
    public function attemptUserLogin(string $email, string $password): bool
    {
        $result = $this->identity->authenticateUserCredentials($email, $password);

        if (! $result->isSuccess()) {
            return false;
        }

        $identity = (array) ($result->data()['identity'] ?? []);
        $this->session->regenerate();
        $this->session->put('auth.user', $identity);

        return true;
    }

    /**
     * Oturumdaki aktif kullaniciyi dondurur.
     *
     * @return array|null
     */
    public function user(): ?array
    {
        $user = $this->session->get('auth.user');

        return is_array($user) ? $user : null;
    }

    /**
     * Kullanici giris yapmis mi bilgisini verir.
     *
     * @return bool
     */
    public function checkUser(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Kullanici oturumunu kapatir.
     *
     * @return void
     */
    public function logoutUser(): void
    {
        $this->session->forget('auth.user');
        $this->session->regenerate();
    }
}
