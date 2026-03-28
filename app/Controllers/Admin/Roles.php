<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\RoleManagementPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\RawValue;
use Core\View\View;

/**
 * Admin rol ve yetki ekranlarini yonetir.
 */
final class Roles extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'roles.view',
            'edit' => 'roles.edit',
            'update' => 'roles.edit',
        ];
    }

    public function __construct(
        View $view,
        private readonly RoleManagementPageService $roles
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->renderPageResult(
            $this->roles->index($request->path()),
            'pages/roles/index',
            null,
            fn (array $data): array => $this->prepareIndexData($data),
            '/admin/login'
        );
    }

    public function edit(Request $request, string $id = ''): Response
    {
        return $this->renderPageResult(
            $this->roles->editForm($request->path(), (int) $id),
            'pages/roles/form',
            null,
            fn (array $data): array => $this->prepareFormData($data),
            '/admin/roles'
        );
    }

    public function update(Request $request, string $id = ''): Response
    {
        return $this->redirectResult($this->roles->update((int) $id, $request->all()), '/admin/roles');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareIndexData(array $data): array
    {
        $data['alert_html'] = new RawValue($this->buildAlert(
            (string) ($data['flash_message'] ?? ''),
            (string) ($data['error_message'] ?? '')
        ));
        $data['roles_rows_html'] = new RawValue($this->buildRows((array) ($data['roles'] ?? [])));

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareFormData(array $data): array
    {
        $data['alert_html'] = new RawValue($this->buildAlert('', (string) ($data['error_message'] ?? '')));
        $data['permission_groups_html'] = new RawValue($this->buildPermissionGroups(
            (array) ($data['permission_catalog'] ?? []),
            (array) ($data['role_permissions'] ?? [])
        ));

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $roles
     * @return string
     */
    private function buildRows(array $roles): string
    {
        if ($roles === []) {
            return '<tr><td colspan="5" style="padding:18px; color:#94a3b8;">Rol kaydi bulunamadi.</td></tr>';
        }

        $rows = [];

        foreach ($roles as $role) {
            $auth = trim((string) ($role['auth'] ?? ''));
            $permissions = $auth === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $auth))));

            $rows[] = sprintf(
                '<tr>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;"><a href="/admin/roles/edit/%d" style="color:#93c5fd; text-decoration:none;">Duzenle</a></td>
                </tr>',
                (int) ($role['id'] ?? 0),
                htmlspecialchars((string) ($role['name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($auth !== '' ? $auth : '-', ENT_QUOTES, 'UTF-8'),
                count($permissions),
                (int) ($role['id'] ?? 0)
            );
        }

        return implode('', $rows);
    }

    private function buildAlert(string $success, string $error): string
    {
        $blocks = [];

        if ($success !== '') {
            $blocks[] = sprintf(
                '<div style="background:rgba(34,197,94,.14); border:1px solid rgba(34,197,94,.28); color:#dcfce7; border-radius:14px; padding:14px;">%s</div>',
                htmlspecialchars($success, ENT_QUOTES, 'UTF-8')
            );
        }

        if ($error !== '') {
            $blocks[] = sprintf(
                '<div style="background:rgba(239,68,68,.14); border:1px solid rgba(239,68,68,.28); color:#fee2e2; border-radius:14px; padding:14px;">%s</div>',
                htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
            );
        }

        return implode('', $blocks);
    }

    /**
     * @param array<string, array<int, string>> $catalog
     * @param array<int, string> $selected
     * @return string
     */
    private function buildPermissionGroups(array $catalog, array $selected): string
    {
        $html = [];

        foreach ($catalog as $group => $permissions) {
            $items = [];

            foreach ($permissions as $permission) {
                $checked = in_array($permission, $selected, true) ? ' checked' : '';
                $items[] = sprintf(
                    '<label style="display:flex; align-items:center; gap:8px; padding:8px 0;">
                        <input type="checkbox" name="permissions[]" value="%s"%s>
                        <span>%s</span>
                    </label>',
                    htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'),
                    $checked,
                    htmlspecialchars($permission, ENT_QUOTES, 'UTF-8')
                );
            }

            $html[] = sprintf(
                '<div style="padding:16px; background:#1f2937; border:1px solid rgba(148,163,184,.18); border-radius:16px;">
                    <div style="font-weight:700; margin-bottom:8px;">%s</div>
                    %s
                </div>',
                htmlspecialchars(ucfirst($group), ENT_QUOTES, 'UTF-8'),
                implode('', $items)
            );
        }

        return implode('', $html);
    }
}
