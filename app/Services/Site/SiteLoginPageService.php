<?php

declare(strict_types=1);

namespace App\Services\Site;

use App\Requests\Site\SiteLoginRequest;
use App\Services\UserAuthService;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Site kullanici giris ekranini ve akisini yonetir.
 */
final class SiteLoginPageService extends BasePageService
{
    /**
     * @param UserAuthService $auth Site kullanici auth servisi.
     * @param Session $session Flash veri depolamasi.
     * @param Csrf $csrf Form token servisi.
     * @param SiteLoginRequest $loginRequest Login form request nesnesi.
     */
    public function __construct(
        private readonly UserAuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly SiteLoginRequest $loginRequest
    ) {
    }

    /**
     * Site login formu verisini hazirlar.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function form(string $path): ServiceResult
    {
        if ($this->auth->checkUser()) {
            return $this->redirectSuccess('Kullanici zaten giris yapmis.', '/');
        }

        return $this->success('Site kullanici giris formu hazir.', [
            'path' => $path,
            'error_message' => (string) $this->session->getFlash('site_auth.error', ''),
            'email_error' => (string) $this->session->getFlash('site_auth.email_error', ''),
            'password_error' => (string) $this->session->getFlash('site_auth.password_error', ''),
            'old_email' => (string) $this->session->getFlash('site_auth.old_email', ''),
            'csrf_token' => $this->csrf->token(),
        ]);
    }

    /**
     * Site kullanici girisini isler.
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
            $this->flashValidationErrors($this->session, 'site_auth.error', 'Form verilerini kontrol edin.', $validation, [
                'email' => 'site_auth.email_error',
                'password' => 'site_auth.password_error',
            ]);
            $this->session->flash('site_auth.old_email', $email);

            return $this->redirectError('Form verileri hatali.', '/login', 422);
        }

        if (! $this->auth->attemptUserLogin($email, $password)) {
            $this->session->flash('site_auth.error', 'Giris bilgileri hatali.');
            $this->session->flash('site_auth.old_email', $email);

            return $this->redirectError('Kullanici giris bilgileri hatali.', '/login', 401);
        }

        return $this->redirectSuccess('Kullanici girisi basarili.', '/');
    }

    /**
     * Site kullanici oturumunu kapatir.
     *
     * @return ServiceResult
     */
    public function logout(): ServiceResult
    {
        $this->auth->logoutUser();
        $this->session->flash('site_auth.error', 'Oturum kapatildi.');

        return $this->redirectSuccess('Kullanici oturumu kapatildi.', '/login');
    }
}
