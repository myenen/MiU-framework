<?php

declare(strict_types=1);

namespace App\Services;

use Core\Orm\Models;
use Core\Services\BaseService;
use Core\Services\ServiceResult;

/**
 * Kullanici, rol ve profil verilerini servis katmaninda toplar.
 */
final class UserService extends BaseService
{
    /**
     * @param IdentityService $identity Kimlik ve parola yardimcilari.
     */
    public function __construct(
        private readonly IdentityService $identity,
        private readonly AuthorizationService $authorization
    ) {
    }

    /**
     * Ortak lookup verileri
     */

    /**
     * Kullanilabilir rol listesini dondurur.
     *
     * @return ServiceResult
     */
    public function roles(): ServiceResult
    {
        $rows = Models::get('userRole')
            ->orderBy('id', 'ASC')
            ->all();

        return $this->success('Rol listesi hazir.', [
            'roles' => $this->normalizeObjects($rows),
        ]);
    }

    /**
     * Tek bir rol kaydini dondurur.
     *
     * @param int $id Rol id degeri.
     * @return ServiceResult
     */
    public function findRole(int $id): ServiceResult
    {
        $role = Models::get('userRole')
            ->where('id', $id)
            ->first();

        if (! is_object($role)) {
            return $this->error('Rol bulunamadi.', [], 404);
        }

        return $this->success('Rol kaydi hazir.', [
            'role' => $this->normalizeObject($role),
        ]);
    }

    /**
     * Rol kaydini gunceller.
     *
     * @param int $id Rol id degeri.
     * @param array<string, mixed> $payload Gelen form verisi.
     * @return ServiceResult
     */
    public function updateRole(int $id, array $payload): ServiceResult
    {
        $role = Models::get('userRole')
            ->where('id', $id)
            ->first();

        if (! is_object($role)) {
            return $this->error('Rol bulunamadi.', [], 404);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $permissions = $payload['permissions'] ?? [];
        $auth = $this->normalizeRolePermissions($permissions);

        if ($name === '') {
            return $this->error('Rol adi zorunludur.', [], 422);
        }

        if ($permissions !== [] && $auth === '') {
            return $this->error('En az bir gecerli yetki seciniz.', [], 422);
        }

        $role->name = $name;
        $role->auth = $auth;

        $updated = $role->update();

        if ((bool) ($updated->error ?? false)) {
            return $this->error((string) ($updated->msg ?? 'Rol guncellenemedi.'), [], 500);
        }

        return $this->success('Rol guncellendi.', [
            'role_id' => $id,
        ]);
    }

    /**
     * Formlarda kullanilacak durum listesini dondurur.
     *
     * @return ServiceResult
     */
    public function statuses(): ServiceResult
    {
        return $this->success('Durum listesi hazir.', [
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'passive', 'label' => 'Passive'],
            ],
        ]);
    }

    /**
     * Kullanici sorgulari
     */

    /**
     * Kullanici ozet verilerini dondurur.
     *
     * @return ServiceResult
     */
    public function summary(): ServiceResult
    {
        $totalUsers = Models::get('users')->count();
        $activeUsers = Models::get('users')->where('status', 'active')->count();
        $latestUser = Models::get('users')->orderBy('id', 'DESC')->first();

        return $this->success('Kullanici ozeti hazir.', [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'latest_user_name' => $this->displayName(is_object($latestUser) ? $latestUser : null),
        ]);
    }

    /**
     * Kullanici listesini iliskili rol ve profil verileriyle dondurur.
     *
     * @param int $limit Maksimum kullanici adedi.
     * @return ServiceResult
     */
    public function listUsers(int $limit = 20, array $filters = []): ServiceResult
    {
        $query = Models::get('users')
            ->orderBy('id', 'DESC');

        $statusFilter = trim((string) ($filters['status'] ?? ''));
        if ($statusFilter !== '') {
            $query = $query->where('status', $statusFilter);
        }

        $rows = $query->all();

        $users = $this->enrichUsers($this->normalizeObjects($rows));
        $search = mb_strtolower(trim((string) ($filters['q'] ?? '')));

        if ($search !== '') {
            $users = array_values(array_filter($users, static function (array $user) use ($search): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($user['display_name'] ?? ''),
                    (string) ($user['email'] ?? ''),
                    (string) ($user['city'] ?? ''),
                    (string) ($user['phone'] ?? ''),
                ]));

                return str_contains($haystack, $search);
            }));
        }

        $users = array_slice($users, 0, max(1, $limit));

        return $this->success('Kullanici listesi hazir.', [
            'users' => $users,
            'summary' => $this->summary()->data(),
            'filters' => [
                'q' => (string) ($filters['q'] ?? ''),
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Tek bir kullaniciyi iliskili verileriyle dondurur.
     *
     * @param int $id Kullanici id degeri.
     * @return ServiceResult
     */
    public function findUser(int $id): ServiceResult
    {
        $user = Models::get('users')
            ->where('id', $id)
            ->first();

        if (! is_object($user)) {
            return $this->error('Kullanici bulunamadi.', [], 404);
        }

        $users = $this->enrichUsers([$this->normalizeObject($user)]);

        return $this->success('Kullanici kaydi hazir.', [
            'user' => $users[0] ?? [],
        ]);
    }

    /**
     * Kullanici yazma islemleri
     */

    /**
     * Yeni kullanici ve profil kaydi olusturur.
     *
     * @param array<string, mixed> $payload Form veya API verisi.
     * @return ServiceResult
     */
    public function createUser(array $payload): ServiceResult
    {
        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));

        if ($this->emailExists($email)) {
            return $this->error('Bu e-posta adresi zaten kullaniliyor.', [], 422);
        }

        if (! $this->roleIsValid((int) ($payload['role'] ?? 0))) {
            return $this->error('Gecerli bir rol seciniz.', [], 422);
        }

        $timestamp = time();
        $user = $this->buildNewUserModel($payload, $email, $timestamp);

        $createdId = $user->save();

        if (is_object($createdId) && (bool) ($createdId->error ?? false)) {
            return $this->error((string) ($createdId->msg ?? 'Kullanici olusturulamadi.'), [], 500);
        }

        $userId = (int) ($user->id ?? $createdId);
        $this->saveProfile($userId, $payload, $timestamp);

        return $this->success('Kullanici olusturuldu.', [
            'user_id' => $userId,
        ], 201);
    }

    /**
     * Var olan kullaniciyi gunceller.
     *
     * @param int $id Kullanici id degeri.
     * @param array<string, mixed> $payload Form veya API verisi.
     * @return ServiceResult
     */
    public function updateUser(int $id, array $payload): ServiceResult
    {
        $user = Models::get('users')
            ->where('id', $id)
            ->first();

        if (! is_object($user)) {
            return $this->error('Kullanici bulunamadi.', [], 404);
        }

        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));

        if ($this->emailExists($email, $id)) {
            return $this->error('Bu e-posta adresi zaten kullaniliyor.', [], 422);
        }

        if (! $this->roleIsValid((int) ($payload['role'] ?? 0))) {
            return $this->error('Gecerli bir rol seciniz.', [], 422);
        }

        $this->fillUserModel($user, $payload, $email);
        $this->applyOptionalPassword($user, (string) ($payload['password'] ?? ''));

        $updated = $user->update();

        if ((bool) ($updated->error ?? false)) {
            return $this->error((string) ($updated->msg ?? 'Kullanici guncellenemedi.'), [], 500);
        }

        $this->saveProfile($id, $payload, time());

        return $this->success('Kullanici guncellendi.', [
            'user_id' => $id,
        ]);
    }

    /**
     * Kullanici kaydini siler.
     *
     * @param int $id Kullanici id degeri.
     * @return ServiceResult
     */
    public function deleteUser(int $id): ServiceResult
    {
        $user = Models::get('users')
            ->where('id', $id)
            ->first();

        if (! is_object($user)) {
            return $this->error('Kullanici bulunamadi.', [], 404);
        }

        Models::get('userProfile')->runSQL(
            'DELETE FROM userProfile WHERE userId = :user_id',
            [':user_id' => $id]
        );

        $deleted = Models::get('users')->runSQL(
            'DELETE FROM users WHERE id = :id',
            [':id' => $id]
        );

        if (is_object($deleted) && (bool) ($deleted->error ?? false)) {
            return $this->error((string) ($deleted->msg ?? 'Kullanici silinemedi.'), [], 500);
        }

        return $this->success('Kullanici silindi.');
    }

    /**
     * Rol ve profil yardimcilari
     */

    /**
     * Kullanici satirlarini rol ve profil verileriyle zenginlestirir.
     *
     * @param array<int, array<string, mixed>> $users Ham kullanici satirlari.
     * @return array<int, array<string, mixed>>
     */
    private function enrichUsers(array $users): array
    {
        if ($users === []) {
            return [];
        }

        $profilesByUserId = $this->profilesByUserId($users);
        $rolesById = $this->rolesById($users);

        return array_map(function (array $user) use ($profilesByUserId, $rolesById): array {
            $userId = (int) ($user['id'] ?? 0);
            $roleId = (int) ($user['role'] ?? 0);
            $profile = $profilesByUserId[$userId] ?? [];
            $role = $rolesById[$roleId] ?? [];

            return [
                'id' => $userId,
                'name' => (string) ($user['name'] ?? ''),
                'surname' => (string) ($user['surname'] ?? ''),
                'display_name' => trim(((string) ($user['name'] ?? '')) . ' ' . ((string) ($user['surname'] ?? ''))),
                'email' => (string) ($user['email'] ?? ''),
                'status' => (string) ($user['status'] ?? ''),
                'role_id' => $roleId,
                'role_name' => (string) ($role['name'] ?? ''),
                'role_auth' => (string) ($role['auth'] ?? ''),
                'phone' => (string) ($profile['phone'] ?? ''),
                'city' => (string) ($profile['city'] ?? ''),
                'address' => (string) ($profile['address'] ?? ''),
            ];
        }, $users);
    }

    /**
     * Kullanici listesi icin profil verilerini userId bazinda dondurur.
     *
     * @param array<int, array<string, mixed>> $users Ham kullanici satirlari.
     * @return array<int, array<string, mixed>>
     */
    private function profilesByUserId(array $users): array
    {
        $userIds = array_values(array_filter(array_map(
            static fn (array $user): int => (int) ($user['id'] ?? 0),
            $users
        )));

        if ($userIds === []) {
            return [];
        }

        $profiles = $this->normalizeObjects(
            Models::get('userProfile')
                ->whereIn('userId', $userIds)
                ->all()
        );
        $profilesByUserId = [];

        foreach ($profiles as $profile) {
            $profilesByUserId[(int) ($profile['userId'] ?? 0)] = $profile;
        }

        return $profilesByUserId;
    }

    /**
     * Kullanici listesi icin rol verilerini rol id bazinda dondurur.
     *
     * @param array<int, array<string, mixed>> $users Ham kullanici satirlari.
     * @return array<int, array<string, mixed>>
     */
    private function rolesById(array $users): array
    {
        $roleIds = array_values(array_filter(array_unique(array_map(
            static fn (array $user): int => (int) ($user['role'] ?? 0),
            $users
        ))));

        if ($roleIds === []) {
            return [];
        }

        $roles = $this->normalizeObjects(
            Models::get('userRole')
                ->whereIn('id', $roleIds)
                ->all()
        );
        $rolesById = [];

        foreach ($roles as $role) {
            $rolesById[(int) ($role['id'] ?? 0)] = $role;
        }

        return $rolesById;
    }

    /**
     * Kullanici profil bilgisini ekler veya gunceller.
     *
     * @param int $userId Kullanici id degeri.
     * @param array<string, mixed> $payload Gelen veri.
     * @param int $timestamp Zaman damgasi.
     * @return void
     */
    private function saveProfile(int $userId, array $payload, int $timestamp): void
    {
        $profile = Models::get('userProfile')
            ->where('userId', $userId)
            ->first();

        $params = [
            ':user_id' => $userId,
            ':phone' => trim((string) ($payload['phone'] ?? '')),
            ':city' => trim((string) ($payload['city'] ?? '')),
            ':address' => trim((string) ($payload['address'] ?? '')),
            ':updated_at' => $timestamp,
        ];

        if (! is_object($profile)) {
            Models::get('userProfile')->runSQL(
                'INSERT INTO userProfile (userId, phone, city, address, created_at, updated_at)
                 VALUES (:user_id, :phone, :city, :address, :created_at, :updated_at)',
                $params + [':created_at' => $timestamp]
            );

            return;
        }

        Models::get('userProfile')->runSQL(
            'UPDATE userProfile
             SET phone = :phone, city = :city, address = :address, updated_at = :updated_at
             WHERE userId = :user_id',
            $params
        );
    }

    /**
     * Genel yardimcilar
     */

    /**
     * Yeni kullanici kaydi icin model nesnesini hazirlar.
     *
     * @param array<string, mixed> $payload Form veya API verisi.
     * @param string $email Normalize edilmis e-posta adresi.
     * @param int $timestamp Zaman damgasi.
     * @return object
     */
    private function buildNewUserModel(array $payload, string $email, int $timestamp): object
    {
        $user = Models::get('users');
        $this->fillUserModel($user, $payload, $email);
        $user->password = $this->identity->hashUserPassword((string) ($payload['password'] ?? ''));
        $user->created_at = $timestamp;
        $user->updated_at = $timestamp;

        return $user;
    }

    /**
     * Ortak kullanici alanlarini gelen veriye gore modele yazar.
     *
     * @param object $user Kullanici model nesnesi.
     * @param array<string, mixed> $payload Form veya API verisi.
     * @param string $email Normalize edilmis e-posta adresi.
     * @return void
     */
    private function fillUserModel(object $user, array $payload, string $email): void
    {
        $user->name = trim((string) ($payload['name'] ?? ''));
        $user->surname = trim((string) ($payload['surname'] ?? ''));
        $user->email = $email;
        $user->status = $this->normalizeStatus((string) ($payload['status'] ?? 'active'));

        if ($this->usersHaveRoleColumn()) {
            $user->role = (int) ($payload['role'] ?? 0);
        }
    }

    /**
     * Guncelleme isleminde parola geldiyse modele yeni sifreyi uygular.
     *
     * @param object $user Kullanici model nesnesi.
     * @param string $password Duz parola.
     * @return void
     */
    private function applyOptionalPassword(object $user, string $password): void
    {
        $password = trim($password);

        if ($password === '') {
            return;
        }

        $user->password = $this->identity->hashUserPassword($password);
    }

    /**
     * E-posta adresinin daha once kullanilip kullanilmadigini kontrol eder.
     *
     * @param string $email Aranacak e-posta adresi.
     * @param int|null $exceptId Haric tutulacak kullanici id degeri.
     * @return bool
     */
    private function emailExists(string $email, ?int $exceptId = null): bool
    {
        if ($email === '') {
            return false;
        }

        $user = Models::get('users')
            ->where('email', $email)
            ->first();

        if (! is_object($user)) {
            return false;
        }

        if ($exceptId !== null && (int) ($user->id ?? 0) === $exceptId) {
            return false;
        }

        return true;
    }

    /**
     * Rol id degerinin gecerli olup olmadigini belirtir.
     *
     * @param int $roleId Rol id degeri.
     * @return bool
     */
    private function roleIsValid(int $roleId): bool
    {
        if (! $this->usersHaveRoleColumn()) {
            return true;
        }

        if ($roleId <= 0) {
            return false;
        }

        return Models::get('userRole')
            ->where('id', $roleId)
            ->exists();
    }

    /**
     * Users tablosunda role kolonu olup olmadigini kontrol eder.
     *
     * @return bool
     */
    private function usersHaveRoleColumn(): bool
    {
        $model = Models::get('users');

        return property_exists($model, 'role');
    }

    /**
     * Durum degerini izin verilen degerlere indirger.
     *
     * @param string $status Gelen durum metni.
     * @return string
     */
    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['active', 'passive'], true) ? $status : 'active';
    }

    /**
     * Rol yetkilerini standart auth metnine cevirir.
     *
     * @param mixed $permissions Gelen permission dizisi.
     * @return string
     */
    private function normalizeRolePermissions(mixed $permissions): string
    {
        if (! is_array($permissions)) {
            return '';
        }

        return implode(',', $this->authorization->sanitizePermissions(array_map(
            static fn (mixed $permission): string => (string) $permission,
            $permissions
        )));
    }

    /**
     * Nesne ya da nesne listesini dizi listesine donusturur.
     *
     * @param object|array|false $rows Model sonucu.
     * @return array<int, array<string, mixed>>
     */
    private function normalizeObjects(object|array|false $rows): array
    {
        if ($rows === false) {
            return [];
        }

        $items = is_array($rows) ? $rows : [$rows];
        $normalized = [];

        foreach ($items as $item) {
            if (! is_object($item)) {
                continue;
            }

            $normalized[] = $this->normalizeObject($item);
        }

        return $normalized;
    }

    /**
     * Tek bir model nesnesini diziye cevirir.
     *
     * @param object $item Model nesnesi.
     * @return array<string, mixed>
     */
    private function normalizeObject(object $item): array
    {
        return get_object_vars($item);
    }

    /**
     * Tek kullanici nesnesinden gorunen ad uretir.
     *
     * @param object|null $user Kullanici nesnesi.
     * @return string
     */
    private function displayName(?object $user): string
    {
        if ($user === null) {
            return '-';
        }

        $name = trim(((string) ($user->name ?? '')) . ' ' . ((string) ($user->surname ?? '')));

        return $name !== '' ? $name : '-';
    }
}
