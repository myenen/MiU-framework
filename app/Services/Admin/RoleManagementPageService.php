<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Requests\Admin\RoleUpdateRequest;
use App\Services\AuthService;
use App\Services\AuthorizationService;
use App\Services\UserService;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Admin rol ve yetki ekranlarinin servis katmanini yonetir.
 */
final class RoleManagementPageService extends BasePageService
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly AuthorizationService $authorization,
        private readonly UserService $users,
        private readonly RoleUpdateRequest $updateRequest
    ) {
    }

    /**
     * @param string $path
     * @return ServiceResult
     */
    public function index(string $path): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $roles = (array) ($this->users->roles()->data()['roles'] ?? []);

        return $this->success('Rol liste sayfasi hazir.', [
            'path' => $path,
            'roles' => $roles,
            'flash_message' => (string) $this->session->getFlash('roles.success', ''),
            'error_message' => (string) $this->session->getFlash('roles.error', ''),
        ]);
    }

    /**
     * @param string $path
     * @param int $id
     * @return ServiceResult
     */
    public function editForm(string $path, int $id): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $roleResult = $this->users->findRole($id);
        if (! $roleResult->isSuccess()) {
            return $this->redirectError('Rol bulunamadi.', '/admin/roles', 404);
        }

        $role = (array) ($roleResult->data()['role'] ?? []);
        $selectedPermissions = $this->selectedPermissions((string) ($role['auth'] ?? ''));

        return $this->success('Rol duzenleme formu hazir.', [
            'path' => $path,
            'csrf_token' => $this->csrf->token(),
            'error_message' => (string) $this->session->getFlash('roles.error', ''),
            'form_action' => '/admin/roles/update/' . $id,
            'role_name' => (string) ($this->session->getFlash('roles.old.name', $role['name'] ?? '')),
            'role_permissions' => $this->session->getFlash('roles.old.permissions', $selectedPermissions),
            'role_id' => (string) ($role['id'] ?? $id),
            'permission_catalog' => $this->authorization->permissionCatalog(),
        ]);
    }

    /**
     * @param int $id
     * @param array<string, mixed> $payload
     * @return ServiceResult
     */
    public function update(int $id, array $payload): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $this->session->flash('roles.old.name', (string) ($payload['name'] ?? ''));
        $this->session->flash('roles.old.permissions', $payload['permissions'] ?? []);

        $validation = $this->updateRequest->validate($payload);
        if ($validation->fails()) {
            $this->session->flash('roles.error', $validation->first('name'));

            return $this->redirectError('Form verileri hatali.', '/admin/roles/edit/' . $id, 422);
        }

        $result = $this->users->updateRole($id, $payload);
        if (! $result->isSuccess()) {
            $this->session->flash('roles.error', $result->message());

            return $this->redirectError($result->message(), '/admin/roles/edit/' . $id, $result->status());
        }

        $this->session->getFlash('roles.old.name', '');
        $this->session->getFlash('roles.old.permissions', []);
        $this->session->flash('roles.success', 'Rol guncellendi.');

        return $this->redirectSuccess('Rol guncellendi.', '/admin/roles');
    }

    /**
     * @return ServiceResult
     */
    private function guard(): ServiceResult
    {
        if ($this->auth->checkAdmin()) {
            return $this->success('Admin oturumu gecerli.');
        }

        return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
    }

    /**
     * @param string $auth
     * @return array<int, string>
     */
    private function selectedPermissions(string $auth): array
    {
        $parts = $auth === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $auth))));

        return $this->authorization->sanitizePermissions($parts);
    }
}
