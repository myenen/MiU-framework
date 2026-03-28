<?php

declare(strict_types=1);

namespace App\Services;

use Core\Orm\Models;

/**
 * Rol kayitlarindaki auth alanini yorumlayip yetki kontrolu yapar.
 */
final class AuthorizationService
{
    /**
     * Sistem genelinde kullanilabilir permission katalogunu dondurur.
     *
     * @return array<string, array<int, string>>
     */
    public function permissionCatalog(): array
    {
        return [
            'dashboard' => ['dashboard.view'],
            'users' => ['users.view', 'users.edit', 'users.delete'],
            'roles' => ['roles.view', 'roles.edit'],
            'logs' => ['logs.view'],
            'translations' => ['translations.view', 'translations.edit'],
            'uploads' => ['uploads.view', 'uploads.delete'],
            'docs' => ['docs.view'],
        ];
    }

    /**
     * Gecerli tum permission degerlerini tek liste halinde dondurur.
     *
     * @return array<int, string>
     */
    public function validPermissions(): array
    {
        return array_values(array_merge(...array_values($this->permissionCatalog())));
    }

    /**
     * Rol icin tanimli yetki listesini dondurur.
     *
     * @param int $roleId Rol id degeri.
     * @return array<int, string>
     */
    public function permissionsForRole(int $roleId): array
    {
        if ($roleId <= 0) {
            return [];
        }

        $role = Models::get('userRole')
            ->where('id', $roleId)
            ->first();

        if (! is_object($role)) {
            return [];
        }

        return $this->parsePermissions((string) ($role->auth ?? ''));
    }

    /**
     * Kimlik verisindeki rol ve permission alanlarina gore yetki kontrolu yapar.
     *
     * @param array<string, mixed>|null $identity Oturum veya token kimlik verisi.
     * @param string|array<int, string> $required Gerekli yetki veya yetki listesi.
     * @return bool
     */
    public function identityCan(?array $identity, string|array $required): bool
    {
        if ($identity === null) {
            return false;
        }

        $requiredPermissions = $this->normalizeRequiredPermissions($required);

        if ($requiredPermissions === []) {
            return true;
        }

        $available = $identity['permissions'] ?? [];

        if (! is_array($available) || $available === []) {
            $available = $this->permissionsForRole((int) ($identity['role'] ?? 0));
        }

        $available = array_values(array_filter(array_map(
            static fn (mixed $permission): string => trim((string) $permission),
            is_array($available) ? $available : []
        )));

        foreach ($requiredPermissions as $permission) {
            if ($this->permissionMatches($available, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rol auth metnini yetki listesine cevirir.
     *
     * @param string $auth Rol kaydindaki auth alani.
     * @return array<int, string>
     */
    private function parsePermissions(string $auth): array
    {
        $auth = trim($auth);

        if ($auth === '') {
            return [];
        }

        $parts = preg_split('/[\s,;|]+/', $auth) ?: [];
        $parts = array_values(array_filter(array_map(
            static fn (string $permission): string => trim($permission),
            $parts
        ), static fn (string $permission): bool => $permission !== ''));

        return array_values(array_unique($parts));
    }

    /**
     * Gelen permission listesini gecerli katalog ile temizler.
     *
     * @param array<int, string> $permissions Ham permission listesi.
     * @return array<int, string>
     */
    public function sanitizePermissions(array $permissions): array
    {
        $valid = $this->validPermissions();
        $normalized = array_values(array_filter(array_map(
            static fn (string $permission): string => trim($permission),
            $permissions
        ), static fn (string $permission): bool => $permission !== ''));

        return array_values(array_unique(array_values(array_intersect($normalized, $valid))));
    }

    /**
     * Gerekli yetki tanimini listeye cevirir.
     *
     * @param string|array<int, string> $required Gerekli yetki tanimi.
     * @return array<int, string>
     */
    private function normalizeRequiredPermissions(string|array $required): array
    {
        $values = is_array($required) ? $required : [$required];

        return array_values(array_filter(array_map(
            static fn (string $permission): string => trim($permission),
            $values
        ), static fn (string $permission): bool => $permission !== ''));
    }

    /**
     * Kullanici yetkileri icinde gerekli yetkinin karsilanip karsilanmadigini kontrol eder.
     *
     * @param array<int, string> $available Kullanilabilir yetkiler.
     * @param string $required Gerekli yetki.
     * @return bool
     */
    private function permissionMatches(array $available, string $required): bool
    {
        foreach ($available as $permission) {
            if ($permission === 'all' || $permission === '*') {
                return true;
            }

            if ($permission === $required) {
                return true;
            }

            if (str_ends_with($permission, '.*')) {
                $prefix = substr($permission, 0, -2);

                if ($prefix !== '' && str_starts_with($required, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }
}
