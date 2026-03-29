<?php

declare(strict_types=1);

namespace App\Services\Site;

use App\Requests\Site\SiteLoginRequest;
use App\Services\UserAuthService;
use Core\RateLimit\RateLimiter;
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
        private readonly SiteLoginRequest $loginRequest,
        private readonly RateLimiter $rateLimiter,
        private readonly array $securityConfig = []
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
    public function login(array $payload, string $ipAddress = ''): ServiceResult
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $limitState = $this->guardLoginAttempt('site', $email, $ipAddress);

        if ($limitState !== null) {
            $this->session->flash('site_auth.error', 'Cok fazla giris denemesi yaptiniz. Lutfen daha sonra tekrar deneyin.');
            $this->session->flash('site_auth.old_email', $email);

            return $this->redirectError('Cok fazla giris denemesi.', '/login', 429, $limitState);
        }

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

        $this->clearLoginAttemptGuard('site', $email, $ipAddress);

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

    /**
     * @param string $realm Rate limit alani.
     * @param string $email Giris denemesinde kullanilan e-posta.
     * @param string $ipAddress Istek IP adresi.
     * @return array<string, int|bool>|null
     */
    private function guardLoginAttempt(string $realm, string $email, string $ipAddress): ?array
    {
        $config = $this->loginRateLimitConfig($realm);

        if (! (bool) ($config['enabled'] ?? true)) {
            return null;
        }

        $state = $this->rateLimiter->hit(
            $this->loginAttemptKey($realm, $email, $ipAddress),
            max(1, (int) ($config['max_attempts'] ?? 5)),
            max(1, (int) ($config['decay_seconds'] ?? 300))
        );

        return (bool) ($state['allowed'] ?? false) ? null : $state;
    }

    private function clearLoginAttemptGuard(string $realm, string $email, string $ipAddress): void
    {
        $this->rateLimiter->clear($this->loginAttemptKey($realm, $email, $ipAddress));
    }

    /**
     * @return array<string, mixed>
     */
    private function loginRateLimitConfig(string $realm): array
    {
        $root = is_array($this->securityConfig['login_rate_limit'] ?? null) ? $this->securityConfig['login_rate_limit'] : [];

        return is_array($root[$realm] ?? null) ? $root[$realm] : [];
    }

    private function loginAttemptKey(string $realm, string $email, string $ipAddress): string
    {
        return 'login-attempt:' . $realm . ':' . sha1(mb_strtolower(trim($email)) . '|' . trim($ipAddress));
    }
}
