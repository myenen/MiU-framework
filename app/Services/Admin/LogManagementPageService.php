<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\AuthService;
use Core\Orm\Models;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;

/**
 * Admin log listeleme ekraninin servis katmanini yonetir.
 */
final class LogManagementPageService extends BasePageService
{
    public function __construct(
        private readonly AuthService $auth
    ) {
    }

    /**
     * @param string $path
     * @param array<string, mixed> $filters
     * @return ServiceResult
     */
    public function index(string $path, array $filters = []): ServiceResult
    {
        if (! $this->auth->checkAdmin()) {
            return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
        }

        $logs = $this->normalizeRows(
            Models::get('log')
                ->orderBy('id', 'DESC')
                ->limit(250)
                ->all()
        );

        $method = strtoupper(trim((string) ($filters['method'] ?? '')));
        $status = trim((string) ($filters['status'] ?? ''));
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));

        $logs = array_values(array_filter($logs, static function (array $row) use ($method, $status, $query): bool {
            if ($method !== '' && strtoupper((string) ($row['method'] ?? '')) !== $method) {
                return false;
            }

            if ($status !== '' && (string) ($row['response_status'] ?? '') !== $status) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(implode(' ', [
                (string) ($row['path'] ?? ''),
                (string) ($row['ip_address'] ?? ''),
                (string) ($row['user_agent'] ?? ''),
                (string) ($row['error_message'] ?? ''),
            ]));

            return str_contains($haystack, $query);
        }));

        return $this->success('Log listesi hazir.', [
            'path' => $path,
            'logs' => array_slice($logs, 0, 200),
            'filters' => [
                'q' => (string) ($filters['q'] ?? ''),
                'method' => $method,
                'status' => $status,
            ],
            'summary' => [
                'total' => count($logs),
                'errors' => count(array_filter($logs, static fn (array $log): bool => (int) ($log['response_status'] ?? 0) >= 400)),
            ],
        ]);
    }

    /**
     * @param string $path
     * @param int $id
     * @return ServiceResult
     */
    public function detail(string $path, int $id): ServiceResult
    {
        if (! $this->auth->checkAdmin()) {
            return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
        }

        $log = Models::get('log')->where('id', $id)->first();

        if (! is_object($log)) {
            return $this->redirectError('Log kaydi bulunamadi.', '/admin/logs', 404);
        }

        return $this->success('Log detay sayfasi hazir.', [
            'path' => $path,
            'log' => get_object_vars($log),
        ]);
    }

    /**
     * @param object|array|false $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(object|array|false $rows): array
    {
        if ($rows === false) {
            return [];
        }

        $items = is_array($rows) ? $rows : [$rows];
        $normalized = [];

        foreach ($items as $item) {
            if (is_object($item)) {
                $normalized[] = get_object_vars($item);
            }
        }

        return $normalized;
    }
}
