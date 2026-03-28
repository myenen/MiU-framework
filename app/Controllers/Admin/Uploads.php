<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuthService;
use App\Services\Admin\UploadManagementPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\RawValue;
use Core\View\View;

/**
 * Admin upload ekranlarini yonetir.
 */
final class Uploads extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'uploads.view',
            'delete' => 'uploads.delete',
        ];
    }

    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly UploadManagementPageService $uploads
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->renderPageResult(
            $this->uploads->index($request->path(), $request->queryAll()),
            'pages/uploads/index',
            null,
            fn (array $data): array => $this->prepareData($data),
            '/admin/login'
        );
    }

    public function delete(Request $request, string $id = ''): Response
    {
        return $this->redirectResult($this->uploads->delete((int) $id, dirname(__DIR__, 3)), '/admin/uploads');
    }

    private function prepareData(array $data): array
    {
        $admin = $this->auth->admin() ?? [];
        $permissions = is_array($admin['permissions'] ?? null) ? $admin['permissions'] : [];
        $canManageUploads = in_array('all', $permissions, true)
            || in_array('*', $permissions, true)
            || in_array('uploads.delete', $permissions, true)
            || in_array('uploads.*', $permissions, true);
        $data['alert_html'] = new RawValue($this->buildAlert(
            (string) ($data['flash_message'] ?? ''),
            (string) ($data['error_message'] ?? '')
        ));
        $data['uploads_rows_html'] = new RawValue($this->buildRows(
            (array) ($data['uploads'] ?? []),
            (string) ($data['csrf_token'] ?? ''),
            $canManageUploads
        ));
        $data['filter_query'] = (string) (($data['filters'] ?? [])['q'] ?? '');
        $data['filter_channel'] = (string) (($data['filters'] ?? [])['channel'] ?? '');

        return $data;
    }

    private function buildRows(array $uploads, string $csrfToken, bool $canManageUploads): string
    {
        if ($uploads === []) {
            return '<tr><td colspan="7" style="padding:18px; color:#94a3b8;">Upload kaydi bulunamadi.</td></tr>';
        }

        $html = [];

        foreach ($uploads as $upload) {
            $actionsHtml = $canManageUploads
                ? sprintf(
                    '<form method="post" action="/admin/uploads/delete/%d" style="display:inline-block; margin:0;">
                        <input type="hidden" name="_token" value="%s">
                        <button type="submit" style="border:none; background:transparent; color:#fca5a5; cursor:pointer; padding:0;">Sil</button>
                    </form>',
                    (int) ($upload['id'] ?? 0),
                    htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')
                )
                : '<span style="color:#94a3b8;">-</span>';
            $html[] = sprintf(
                '<tr>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;"><a href="%s" target="_blank" style="color:#93c5fd; text-decoration:none;">Ac</a></td>
                    <td style="padding:14px;">%s</td>
                </tr>',
                (int) ($upload['id'] ?? 0),
                htmlspecialchars((string) ($upload['channel'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($upload['original_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($upload['stored_name'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                (int) ($upload['size'] ?? 0),
                htmlspecialchars((string) ($upload['public_path'] ?? '#'), ENT_QUOTES, 'UTF-8'),
                $actionsHtml
            );
        }

        return implode('', $html);
    }

    private function buildAlert(string $success, string $error): string
    {
        $blocks = [];

        if ($success !== '') {
            $blocks[] = sprintf('<div style="background:rgba(34,197,94,.14); border:1px solid rgba(34,197,94,.28); color:#dcfce7; border-radius:14px; padding:14px;">%s</div>', htmlspecialchars($success, ENT_QUOTES, 'UTF-8'));
        }

        if ($error !== '') {
            $blocks[] = sprintf('<div style="background:rgba(239,68,68,.14); border:1px solid rgba(239,68,68,.28); color:#fee2e2; border-radius:14px; padding:14px;">%s</div>', htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
        }

        return implode('', $blocks);
    }
}
