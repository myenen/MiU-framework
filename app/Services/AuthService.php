<?php

declare(strict_types=1);

namespace App\Services;

use Core\Session;

/**
 * Admin kimlik dogrulama durumunu ve bilgi kontrolunu yonetir.
 */
final class AuthService
{
    /**
     * @param Session $session Dogrulanmis admin verisi icin oturum deposu.
     * @param IdentityService $identity Ortak kimlik dogrulama servisi.
     */
    public function __construct(
        private readonly Session $session,
        private readonly IdentityService $identity
    ) {
    }

    /**
     * Admin kullanicisini e-posta ve parola ile dogrulamayi dener.
     *
     * @param string $email Gonderilen e-posta adresi.
     * @param string $password Gonderilen parola.
     * @return bool
     */
    public function attemptAdminLogin(string $email, string $password): bool
    {
        $result = $this->identity->authenticateAdminCredentials($email, $password);

        if (! $result->isSuccess()) {
            return false;
        }

        $identity = (array) ($result->data()['identity'] ?? []);

        $this->session->regenerate();
        $this->session->put('auth.admin', [
            'id' => (int) ($identity['id'] ?? 0),
            'email' => (string) ($identity['email'] ?? ''),
            'name' => (string) ($identity['name'] ?? 'Admin'),
            'role' => (string) ($identity['role'] ?? 'admin'),
            'permissions' => is_array($identity['permissions'] ?? null) ? $identity['permissions'] : [],
        ]);

        return true;
    }

    /**
     * Oturumdaki mevcut dogrulanmis admin verisini dondurur.
     *
     * @return array|null
     */
    public function admin(): ?array
    {
        $admin = $this->session->get('auth.admin');

        return is_array($admin) ? $admin : null;
    }

    /**
     * Bir admin kullanicisinin su anda dogrulanmis olup olmadigini belirtir.
     *
     * @return bool
     */
    public function checkAdmin(): bool
    {
        return $this->admin() !== null;
    }

    /**
     * Mevcut admin kullanicisinin oturumunu kapatir.
     */
    public function logoutAdmin(): void
    {
        $this->session->forget('auth.admin');
        $this->session->regenerate();
    }
}
