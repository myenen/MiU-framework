<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuthService;
use App\Services\Admin\UserManagementPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\RawValue;
use Core\View\View;

/**
 * Admin kullanici yonetim ekranlarini yonetir.
 */
final class Users extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'users.view',
            'create' => 'users.edit',
            'store' => 'users.edit',
            'edit' => 'users.edit',
            'update' => 'users.edit',
            'delete' => 'users.delete',
        ];
    }

    /**
     * @param View $view Admin view goruntuleyicisi.
     * @param UserManagementPageService $userPages Kullanici yonetim servis katmani.
     */
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly UserManagementPageService $userPages
    ) {
        parent::__construct($view);
    }

    /**
     * Kullanici liste ekranini gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->renderPageResult(
            $this->userPages->index($request->path(), $request->queryAll()),
            'pages/users/index',
            null,
            fn (array $data): array => $this->prepareIndexData($data),
            '/admin/login'
        );
    }

    /**
     * Yeni kullanici formunu gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->renderPageResult(
            $this->userPages->createForm($request->path()),
            'pages/users/form',
            null,
            fn (array $data): array => $this->prepareFormData($data),
            '/admin/login'
        );
    }

    /**
     * Yeni kullanici olusturma istegini isler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function store(Request $request): Response
    {
        return $this->redirectResult($this->userPages->store($request->all()), '/admin/users');
    }

    /**
     * Kullanici duzenleme formunu gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function edit(Request $request, string $id = ''): Response
    {
        return $this->renderPageResult(
            $this->userPages->editForm($request->path(), (int) $id),
            'pages/users/form',
            null,
            fn (array $data): array => $this->prepareFormData($data),
            '/admin/users'
        );
    }

    /**
     * Var olan kullaniciyi gunceller.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function update(Request $request, string $id = ''): Response
    {
        return $this->redirectResult($this->userPages->update((int) $id, $request->all()), '/admin/users');
    }

    /**
     * Kullanici kaydini siler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string $id Kullanici id segmenti.
     * @return Response
     */
    public function delete(Request $request, string $id = ''): Response
    {
        return $this->redirectResult($this->userPages->delete((int) $id), '/admin/users');
    }

    /**
     * Liste sayfasi icin ham servis verisini view'a uygun hale getirir.
     *
     * @param array<string, mixed> $data Servis sonucu.
     * @return array<string, mixed>
     */
    private function prepareIndexData(array $data): array
    {
        $summary = (array) ($data['summary'] ?? []);
        $filters = (array) ($data['filters'] ?? []);
        $admin = $this->auth->admin() ?? [];
        $permissions = is_array($admin['permissions'] ?? null) ? $admin['permissions'] : [];
        $canEditUsers = in_array('all', $permissions, true)
            || in_array('*', $permissions, true)
            || in_array('users.edit', $permissions, true)
            || in_array('users.*', $permissions, true);
        $canDeleteUsers = in_array('all', $permissions, true)
            || in_array('*', $permissions, true)
            || in_array('users.delete', $permissions, true)
            || in_array('users.*', $permissions, true);
        $data['alert_html'] = new RawValue($this->buildAlert(
            (string) ($data['flash_message'] ?? ''),
            (string) ($data['error_message'] ?? '')
        ));
        $data['users_rows_html'] = new RawValue($this->buildUsersRows(
            (array) ($data['users'] ?? []),
            (string) ($data['csrf_token'] ?? ''),
            $canEditUsers,
            $canDeleteUsers
        ));
        $data['can_edit_users'] = $canEditUsers ? '1' : '0';
        $data['summary_total_users'] = (string) ($summary['total_users'] ?? '0');
        $data['summary_active_users'] = (string) ($summary['active_users'] ?? '0');
        $data['summary_latest_user'] = (string) ($summary['latest_user_name'] ?? '-');
        $data['filter_query'] = (string) ($filters['q'] ?? '');
        $data['filter_status_options_html'] = new RawValue($this->buildStatusOptions([
            ['value' => '', 'label' => 'Tum durumlar'],
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'passive', 'label' => 'Passive'],
        ], (string) ($filters['status'] ?? '')));

        return $data;
    }

    /**
     * Form sayfalari icin ortak view verisini hazirlar.
     *
     * @param array<string, mixed> $data Servis sonucu.
     * @return array<string, mixed>
     */
    private function prepareFormData(array $data): array
    {
        $data['alert_html'] = new RawValue($this->buildAlert('', (string) ($data['error_message'] ?? '')));
        $data['role_options_html'] = new RawValue($this->buildRoleOptions(
            (array) ($data['roles'] ?? []),
            (string) ($data['role_value'] ?? '')
        ));
        $data['status_options_html'] = new RawValue($this->buildStatusOptions(
            (array) ($data['statuses'] ?? []),
            (string) ($data['status_value'] ?? 'active')
        ));

        return $data;
    }

    /**
     * Kullanici tablosu satirlarini olusturur.
     *
     * @param array<int, array<string, mixed>> $users Liste verisi.
     * @param string $csrfToken Silme formlari icin CSRF token.
     * @param bool $canEditUsers Satir duzenleme aksiyonlarinin gosterilip gosterilmeyecegi.
     * @param bool $canDeleteUsers Satir silme aksiyonlarinin gosterilip gosterilmeyecegi.
     * @return string
     */
    private function buildUsersRows(array $users, string $csrfToken, bool $canEditUsers, bool $canDeleteUsers): string
    {
        if ($users === []) {
            return '<tr><td colspan="7" style="padding:18px; color:#94a3b8;">Kullanici kaydi bulunamadi.</td></tr>';
        }

        $rows = [];

        foreach ($users as $user) {
            $id = (int) ($user['id'] ?? 0);
            $actions = [];

            if ($canEditUsers) {
                $actions[] = sprintf(
                    '<a href="/admin/users/edit/%d" style="display:inline-block; margin-right:8px; color:#93c5fd; text-decoration:none;">Duzenle</a>',
                    $id
                );
            }

            if ($canDeleteUsers) {
                $actions[] = sprintf(
                    '<form method="post" action="/admin/users/delete/%d" style="display:inline-block; margin:0;">
                        <input type="hidden" name="_token" value="%s">
                        <button type="submit" style="border:none; background:transparent; color:#fca5a5; cursor:pointer; padding:0;">Sil</button>
                    </form>',
                    $id,
                    htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')
                );
            }

            $actionsHtml = $actions === [] ? '<span style="color:#94a3b8;">-</span>' : implode('', $actions);
            $rows[] = sprintf(
                '<tr>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px; white-space:nowrap;">
                        %s
                    </td>
                </tr>',
                $id,
                htmlspecialchars((string) ($user['display_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($user['role_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($user['status'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($user['city'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                $actionsHtml
            );
        }

        return implode('', $rows);
    }

    /**
     * Rol secim kutusu HTML seceneklerini olusturur.
     *
     * @param array<int, array<string, mixed>> $roles Rol listesi.
     * @param string $selected Secili rol degeri.
     * @return string
     */
    private function buildRoleOptions(array $roles, string $selected): string
    {
        $options = [];

        foreach ($roles as $role) {
            $value = (string) ($role['id'] ?? '');
            $isSelected = $value === $selected ? ' selected' : '';
            $options[] = sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $isSelected,
                htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8')
            );
        }

        return implode('', $options);
    }

    /**
     * Durum secim kutusu HTML seceneklerini olusturur.
     *
     * @param array<int, array<string, mixed>> $statuses Durum listesi.
     * @param string $selected Secili durum degeri.
     * @return string
     */
    private function buildStatusOptions(array $statuses, string $selected): string
    {
        $options = [];

        foreach ($statuses as $status) {
            $value = (string) ($status['value'] ?? '');
            $label = (string) ($status['label'] ?? $value);
            $isSelected = $value === $selected ? ' selected' : '';
            $options[] = sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $isSelected,
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
        }

        return implode('', $options);
    }

    /**
     * Basari veya hata mesajini HTML blok olarak olusturur.
     *
     * @param string $success Basari mesaji.
     * @param string $error Hata mesaji.
     * @return string
     */
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
}
