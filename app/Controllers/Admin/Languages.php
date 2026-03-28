<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\LanguageManagementPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\RawValue;
use Core\View\View;

/**
 * Admin dil ve ceviri ekranlarini yonetir.
 */
final class Languages extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'translations.view',
            'edit' => 'translations.edit',
            'update' => 'translations.edit',
        ];
    }

    public function __construct(
        View $view,
        private readonly LanguageManagementPageService $languages
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->renderPageResult(
            $this->languages->index($request->path(), $request->queryAll()),
            'pages/languages/index',
            null,
            fn (array $data): array => $this->prepareIndexData($data),
            '/admin/login'
        );
    }

    public function edit(Request $request, string $id = ''): Response
    {
        return $this->renderPageResult(
            $this->languages->editForm($request->path(), (int) $id),
            'pages/languages/form',
            null,
            fn (array $data): array => $this->prepareFormData($data),
            '/admin/languages'
        );
    }

    public function update(Request $request, string $id = ''): Response
    {
        return $this->redirectResult($this->languages->update((int) $id, $request->all()), '/admin/languages');
    }

    private function prepareIndexData(array $data): array
    {
        $data['alert_html'] = new RawValue($this->buildAlert(
            (string) ($data['flash_message'] ?? ''),
            (string) ($data['error_message'] ?? '')
        ));
        $data['filter_query'] = (string) (($data['filters'] ?? [])['q'] ?? '');
        $data['filter_missing_checked'] = (($data['filters'] ?? [])['missing'] ?? '') === '1' ? 'checked' : '';
        $data['translation_rows_html'] = new RawValue($this->buildRows((array) ($data['translation_groups'] ?? [])));

        return $data;
    }

    private function prepareFormData(array $data): array
    {
        $data['alert_html'] = new RawValue($this->buildAlert('', (string) ($data['error_message'] ?? '')));
        $data['translation_fields_html'] = new RawValue($this->buildFields((array) ($data['translation_fields'] ?? [])));

        return $data;
    }

    private function buildRows(array $rows): string
    {
        if ($rows === []) {
            return '<tr><td colspan="5" style="padding:18px; color:#94a3b8;">Ceviri kaydi bulunamadi.</td></tr>';
        }

        $html = [];

        foreach ($rows as $row) {
            $html[] = sprintf(
                '<tr>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;"><a href="/admin/languages/edit/%d" style="color:#93c5fd; text-decoration:none;">Duzenle</a></td>
                </tr>',
                htmlspecialchars((string) ($row['translation_key'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($row['preview'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                (bool) ($row['has_missing'] ?? false) ? 'Eksik' : 'Tam',
                (int) ($row['languages_count'] ?? 0),
                (int) ($row['id'] ?? 0)
            );
        }

        return implode('', $html);
    }

    private function buildFields(array $fields): string
    {
        $html = [];

        foreach ($fields as $field) {
            $html[] = sprintf(
                '<label style="display:grid; gap:8px;">
                    <span>%s (%s)</span>
                    <textarea name="translations[%d]" rows="3" style="padding:12px 14px; border-radius:12px; border:1px solid rgba(148,163,184,.18); background:#0f172a; color:#f8fafc;">%s</textarea>
                </label>',
                htmlspecialchars((string) ($field['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($field['code'] ?? ''), ENT_QUOTES, 'UTF-8'),
                (int) ($field['id'] ?? 0),
                htmlspecialchars((string) ($field['value'] ?? ''), ENT_QUOTES, 'UTF-8')
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
