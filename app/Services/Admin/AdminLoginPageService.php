<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Requests\Admin\AdminLoginRequest;
use App\Services\AuthService;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Admin giris ekraninin ve giris akislarinin servis katmanini yonetir.
 */
final class AdminLoginPageService extends BasePageService
{
    /**
     * @param AuthService $auth Admin kimlik dogrulama servisi.
     * @param Session $session Flash ve oturum verisi.
     * @param Csrf $csrf CSRF token servisi.
     * @param AdminLoginRequest $loginRequest Login form request nesnesi.
     */
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly AdminLoginRequest $loginRequest
    ) {
    }

    /**
     * Login formu icin view verisini hazirlar.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function form(string $path): ServiceResult
    {
        return $this->success('Admin giris formu hazir.', [
            'error_message' => (string) $this->session->getFlash('auth.error', ''),
            'email_error' => (string) $this->session->getFlash('auth.email_error', ''),
            'password_error' => (string) $this->session->getFlash('auth.password_error', ''),
            'old_email' => (string) $this->session->getFlash('auth.old_email', ''),
            'csrf_token' => $this->csrf->token(),
            'path' => $path,
        ]);
    }

    /**
     * Admin giris denemesini servis katmaninda isler.
     *
     * @param array<string, mixed> $payload Form verisi.
     * @return ServiceResult
     */
    public function login(array $payload): ServiceResult
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $validation = $this->loginRequest->validate($payload);

        if ($validation->fails()) {
            $this->flashValidationErrors($this->session, 'auth.error', 'Form verilerini kontrol edin.', $validation, [
                'email' => 'auth.email_error',
                'password' => 'auth.password_error',
            ]);
            $this->session->flash('auth.old_email', $email);

            return $this->redirectError('Form verileri hatali.', '/admin/login', 422, [
                'errors' => $validation->errors(),
            ]);
        }

        if (! $this->auth->attemptAdminLogin($email, $password)) {
            $this->session->flash('auth.error', 'Giris bilgileri hatali.');
            $this->session->flash('auth.old_email', $email);

            return $this->redirectError('Giris bilgileri hatali.', '/admin/login', 401);
        }

        return $this->redirectSuccess('Admin girisi basarili.', '/admin');
    }

    /**
     * Admin oturumunu kapatir.
     *
     * @return ServiceResult
     */
    public function logout(): ServiceResult
    {
        $this->auth->logoutAdmin();
        $this->session->flash('auth.error', 'Oturum kapatildi.');

        return $this->redirectSuccess('Admin oturumu kapatildi.', '/admin/login');
    }
}
