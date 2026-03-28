<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\LogManagementPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\RawValue;
use Core\View\View;

/**
 * Admin log ekranlarini yonetir.
 */
final class Logs extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'logs.view',
            'show' => 'logs.view',
        ];
    }

    public function __construct(
        View $view,
        private readonly LogManagementPageService $logs
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->renderPageResult(
            $this->logs->index($request->path(), $request->queryAll()),
            'pages/logs/index',
            null,
            fn (array $data): array => $this->prepareData($data),
            '/admin/login'
        );
    }

    public function show(Request $request, string $id = ''): Response
    {
        return $this->renderPageResult(
            $this->logs->detail($request->path(), (int) $id),
            'pages/logs/show',
            null,
            fn (array $data): array => $this->prepareDetailData($data),
            '/admin/logs'
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareData(array $data): array
    {
        $summary = (array) ($data['summary'] ?? []);
        $filters = (array) ($data['filters'] ?? []);
        $data['logs_rows_html'] = new RawValue($this->buildRows((array) ($data['logs'] ?? [])));
        $data['summary_total'] = (string) ($summary['total'] ?? '0');
        $data['summary_errors'] = (string) ($summary['errors'] ?? '0');
        $data['filter_query'] = (string) ($filters['q'] ?? '');
        $data['filter_method'] = (string) ($filters['method'] ?? '');
        $data['filter_status'] = (string) ($filters['status'] ?? '');

        return $data;
    }

    private function prepareDetailData(array $data): array
    {
        $log = (array) ($data['log'] ?? []);
        $data['log_json'] = new RawValue('<pre style="white-space:pre-wrap; color:#cbd5e1;">' . htmlspecialchars(
            (string) json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ENT_QUOTES,
            'UTF-8'
        ) . '</pre>');

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     * @return string
     */
    private function buildRows(array $logs): string
    {
        if ($logs === []) {
            return '<tr><td colspan="7" style="padding:18px; color:#94a3b8;">Log kaydi bulunamadi.</td></tr>';
        }

        $rows = [];

        foreach ($logs as $log) {
            $status = (int) ($log['response_status'] ?? 0);
            $statusColor = $status >= 400 ? '#fca5a5' : '#86efac';
            $rows[] = sprintf(
                '<tr>
                    <td style="padding:14px;">%d</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px; color:%s;">%d</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;">%s</td>
                    <td style="padding:14px;"><a href="/admin/logs/show/%d" style="color:#93c5fd; text-decoration:none;">Detay</a></td>
                </tr>',
                (int) ($log['id'] ?? 0),
                htmlspecialchars((string) ($log['method'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($log['path'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                $statusColor,
                $status,
                htmlspecialchars((string) ($log['ip_address'] ?? '-'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->formatTimestamp($log['created_at'] ?? null), ENT_QUOTES, 'UTF-8'),
                (int) ($log['id'] ?? 0)
            );
        }

        return implode('', $rows);
    }

    private function formatTimestamp(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);

        if (! is_int($timestamp) || $timestamp <= 0) {
            return '-';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
