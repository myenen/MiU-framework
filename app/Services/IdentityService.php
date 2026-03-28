<?php

declare(strict_types=1);

namespace App\Services;

use Core\Http\Request;
use Core\Orm\Models;
use Core\Services\BaseService;
use Core\Services\ServiceResult;

/**
 * Admin, site kullanicisi ve API token kimlik dogrulama mantigini tek yerde toplar.
 */
final class IdentityService extends BaseService
{
    public function __construct(
        private readonly AuthorizationService $authorization,
        private readonly array $apiConfig = []
    ) {
    }

    /**
     * Admin kullanici kimlik bilgilerini dogrular.
     *
     * @param string $email E-posta adresi.
     * @param string $password Girilen parola.
     * @return ServiceResult
     */
    public function authenticateAdminCredentials(string $email, string $password): ServiceResult
    {
        $admin = $this->findActiveAdminByEmail($email);

        if (! is_object($admin)) {
            return $this->error('Admin kullanici bulunamadi.', [], 401);
        }

        if (! $this->verifyUserPassword($password, (string) ($admin->password ?? ''))) {
            return $this->error('Admin giris bilgileri hatali.', [], 401);
        }

        return $this->success('Admin kimlik dogrulamasi basarili.', [
            'identity' => $this->buildAdminIdentity($admin),
        ]);
    }

    /**
     * Site kullanicisi kimlik bilgilerini dogrular.
     *
     * @param string $email E-posta adresi.
     * @param string $password Girilen parola.
     * @return ServiceResult
     */
    public function authenticateUserCredentials(string $email, string $password): ServiceResult
    {
        $user = $this->findActiveUserByEmail($email);

        if (! is_object($user)) {
            return $this->error('Kullanici bulunamadi veya pasif durumda.', [], 401);
        }

        if (! $this->verifyUserPassword($password, (string) ($user->password ?? ''))) {
            return $this->error('Kullanici giris bilgileri hatali.', [], 401);
        }

        return $this->success('Kullanici kimlik dogrulamasi basarili.', [
            'identity' => $this->buildUserIdentity($user),
        ]);
    }

    /**
     * API istegindeki token bilgisini dogrular.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return ServiceResult
     */
    public function authenticateApiRequest(Request $request): ServiceResult
    {
        $headerName = $this->apiHeaderName();
        $providedToken = $request->bearerToken($headerName);

        if ($providedToken === null || $providedToken === '') {
            return $this->error('API token header is missing.', [
                'required_header' => $headerName,
            ], 401);
        }

        $tokenRow = Models::get('api_tokens')
            ->where('token', $providedToken)
            ->first();

        if (! is_object($tokenRow)) {
            return $this->error('API token is invalid.', [
                'required_header' => $headerName,
            ], 401);
        }

        if ((string) ($tokenRow->header_name ?? '') !== $headerName) {
            return $this->error('API token header does not match configuration.', [
                'required_header' => $headerName,
            ], 401);
        }

        if ((int) ($tokenRow->is_active ?? 0) !== 1) {
            return $this->error('API token is inactive.', [], 401);
        }

        $expiresAt = $this->normalizeTimestamp($tokenRow->expires_at ?? null);
        if ($expiresAt !== null && $expiresAt < time()) {
            return $this->error('API oturum suresi doldu. Lutfen tekrar giris yapin.', [], 401);
        }

        $this->touchApiTokenUsage((int) ($tokenRow->id ?? 0));

        return $this->success('API token is valid.', [
            'header' => $headerName,
            'token_name' => (string) ($tokenRow->name ?? ''),
            'token_type' => (string) ($tokenRow->type ?? ''),
            'user_id' => (int) ($tokenRow->user_id ?? 0),
            'identity' => $this->buildApiIdentity((int) ($tokenRow->user_id ?? 0)),
        ]);
    }

    /**
     * Mobil/API istemcileri icin kullanici girisi yapar ve token uretir.
     *
     * @param string $email E-posta adresi.
     * @param string $password Girilen parola.
     * @param string $deviceName Cihaz veya istemci adi.
     * @return ServiceResult
     */
    public function loginUserForApi(string $email, string $password, string $deviceName = 'Mobile App'): ServiceResult
    {
        $auth = $this->authenticateUserCredentials($email, $password);

        if (! $auth->isSuccess()) {
            return $auth;
        }

        $identity = $auth->data()['identity'] ?? [];
        $token = bin2hex(random_bytes(24));
        $name = trim($deviceName) !== '' ? trim($deviceName) : 'Mobile App';
        $timestamp = time();
        $expiresAt = $timestamp + $this->apiTokenTtl();
        $params = [
            ':name' => $name . ' / ' . ((string) ($identity['email'] ?? 'user')),
            ':token' => $token,
            ':header_name' => $this->apiHeaderName(),
            ':type' => 'user_login',
            ':is_active' => 1,
            ':expires_at' => $expiresAt,
            ':created_at' => $timestamp,
            ':updated_at' => $timestamp,
        ];

        if ($this->apiTokensHaveUserId()) {
            Models::get('api_tokens')->runSQL(
                'INSERT INTO api_tokens (user_id, name, token, header_name, type, is_active, expires_at, created_at, updated_at)
                 VALUES (:user_id, :name, :token, :header_name, :type, :is_active, :expires_at, :created_at, :updated_at)',
                $params + [
                    ':user_id' => (int) ($identity['id'] ?? 0),
                ]
            );
        } else {
            Models::get('api_tokens')->runSQL(
                'INSERT INTO api_tokens (name, token, header_name, type, is_active, expires_at, created_at, updated_at)
                 VALUES (:name, :token, :header_name, :type, :is_active, :expires_at, :created_at, :updated_at)',
                $params
            );
        }

        return $this->success('API kullanici girisi basarili.', [
            'header' => $this->apiHeaderName(),
            'token' => $token,
            'identity' => $identity,
        ], 201);
    }

    /**
     * API icin beklenen header adini dondurur.
     *
     * @return string
     */
    public function apiHeaderName(): string
    {
        return (string) ($this->apiConfig['header'] ?? 'Authorization');
    }

    /**
     * API token gecerlilik suresini saniye cinsinden dondurur.
     *
     * @return int
     */
    public function apiTokenTtl(): int
    {
        return max(60, (int) ($this->apiConfig['token_ttl'] ?? 600));
    }

    /**
     * Site kullanicisi parolasini guvenli parola hash'i ile karmalar.
     *
     * @param string $password Duz parola.
     * @return string
     */
    public function hashUserPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Site kullanicisi parolasini kayitli karma ile dogrular.
     *
     * @param string $password Duz parola.
     * @param string $hash Kayitli hash degeri.
     * @return bool
     */
    public function verifyUserPassword(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Api token tablosunda user_id kolonu olup olmadigini kontrol eder.
     *
     * @return bool
     */
    private function apiTokensHaveUserId(): bool
    {
        $model = Models::get('api_tokens');

        return property_exists($model, 'user_id');
    }

    /**
     * E-posta adresine gore aktif admin kullanicisini bulur.
     *
     * @param string $email E-posta adresi.
     * @return object|false
     */
    private function findActiveAdminByEmail(string $email): object|false
    {
        $admin = Models::get('users')
            ->where('email', mb_strtolower(trim($email)))
            ->where('role', 1)
            ->first();

        if (! is_object($admin) || (string) ($admin->status ?? '') !== 'active') {
            return false;
        }

        return $admin;
    }

    /**
     * E-posta adresine gore aktif site kullanicisini bulur.
     *
     * @param string $email E-posta adresi.
     * @return object|false
     */
    private function findActiveUserByEmail(string $email): object|false
    {
        $user = Models::get('users')
            ->where('email', mb_strtolower(trim($email)))
            ->first();

        if (! is_object($user) || (string) ($user->status ?? '') !== 'active') {
            return false;
        }

        return $user;
    }

    /**
     * Admin kimlik verisini ortak dizi yapisina cevirir.
     *
     * @param object $admin Admin model nesnesi.
     * @return array<string, mixed>
     */
    private function buildAdminIdentity(object $admin): array
    {
        $roleId = (int) ($admin->role ?? 1);

        return [
            'id' => (int) ($admin->id ?? 0),
            'email' => (string) ($admin->email ?? ''),
            'name' => trim(((string) ($admin->name ?? '')) . ' ' . ((string) ($admin->surname ?? ''))),
            'role' => $roleId,
            'permissions' => $this->authorization->permissionsForRole($roleId),
        ];
    }

    /**
     * Site kullanici kimlik verisini ortak dizi yapisina cevirir.
     *
     * @param object $user Kullanici model nesnesi.
     * @return array<string, mixed>
     */
    private function buildUserIdentity(object $user): array
    {
        $roleId = (int) ($user->role ?? 0);

        return [
            'id' => (int) ($user->id ?? 0),
            'email' => (string) ($user->email ?? ''),
            'name' => trim(((string) ($user->name ?? '')) . ' ' . ((string) ($user->surname ?? ''))),
            'role' => $roleId,
            'status' => (string) ($user->status ?? ''),
            'permissions' => $this->authorization->permissionsForRole($roleId),
        ];
    }

    /**
     * API token ile iliskili kullanicinin kimlik verisini dondurur.
     *
     * @param int $userId Kullanici id degeri.
     * @return array<string, mixed>
     */
    private function buildApiIdentity(int $userId): array
    {
        if ($userId <= 0) {
            return [
                'id' => 0,
                'email' => '',
                'name' => '',
                'role' => 0,
                'status' => '',
                'permissions' => [],
            ];
        }

        $user = Models::get('users')
            ->where('id', $userId)
            ->first();

        if (! is_object($user)) {
            return [
                'id' => $userId,
                'email' => '',
                'name' => '',
                'role' => 0,
                'status' => '',
                'permissions' => [],
            ];
        }

        return $this->buildUserIdentity($user);
    }

    /**
     * Kullanim sonrasi API token satirini gunceller.
     *
     * @param int $tokenId Token id degeri.
     * @return void
     */
    private function touchApiTokenUsage(int $tokenId): void
    {
        $timestamp = time();
        $expiresAt = $timestamp + $this->apiTokenTtl();
        Models::get('api_tokens')->runSQL(
            'UPDATE api_tokens
             SET last_used_at = :last_used_at, expires_at = :expires_at, updated_at = :updated_at
             WHERE id = :id',
            [
                ':id' => $tokenId,
                ':last_used_at' => $timestamp,
                ':expires_at' => $expiresAt,
                ':updated_at' => $timestamp,
            ]
        );
    }

    /**
     * Tarih/zaman alanini Unix timestamp degerine cevirir.
     *
     * @param mixed $value Ham tablo degeri.
     * @return int|null
     */
    private function normalizeTimestamp(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? null : $timestamp;
    }

}
