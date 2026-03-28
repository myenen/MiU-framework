<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\AuthService;
use Core\Orm\Models;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Admin upload listeleme ve silme ekranlarinin servis katmanini yonetir.
 */
final class UploadManagementPageService extends BasePageService
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf
    ) {
    }

    public function index(string $path, array $filters = []): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $uploads = $this->normalizeRows(
            Models::get('uploads')
                ->orderBy('id', 'DESC')
                ->limit(200)
                ->all()
        );

        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $channel = trim((string) ($filters['channel'] ?? ''));

        $uploads = array_values(array_filter($uploads, static function (array $row) use ($query, $channel): bool {
            if ($channel !== '' && (string) ($row['channel'] ?? '') !== $channel) {
                return false;
            }

            if ($query === '') {
                return true;
            }

            $haystack = mb_strtolower(implode(' ', [
                (string) ($row['original_name'] ?? ''),
                (string) ($row['stored_name'] ?? ''),
                (string) ($row['directory_name'] ?? ''),
                (string) ($row['public_path'] ?? ''),
            ]));

            return str_contains($haystack, $query);
        }));

        return $this->success('Upload listesi hazir.', [
            'path' => $path,
            'csrf_token' => $this->csrf->token(),
            'flash_message' => (string) $this->session->getFlash('uploads.success', ''),
            'error_message' => (string) $this->session->getFlash('uploads.error', ''),
            'uploads' => $uploads,
            'filters' => [
                'q' => (string) ($filters['q'] ?? ''),
                'channel' => $channel,
            ],
        ]);
    }

    public function delete(int $id, string $basePath): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $upload = Models::get('uploads')->where('id', $id)->first();
        if (! is_object($upload)) {
            $this->session->flash('uploads.error', 'Upload kaydi bulunamadi.');

            return $this->redirectError('Upload kaydi bulunamadi.', '/admin/uploads', 404);
        }

        $publicPath = (string) ($upload->public_path ?? '');
        $filePath = rtrim($basePath, '/') . '/public' . $publicPath;

        if ($publicPath !== '' && is_file($filePath)) {
            @unlink($filePath);
        }

        $deleted = $upload->delete();
        if ((bool) ($deleted->error ?? false)) {
            $this->session->flash('uploads.error', (string) ($deleted->msg ?? 'Upload silinemedi.'));

            return $this->redirectError((string) ($deleted->msg ?? 'Upload silinemedi.'), '/admin/uploads', 500);
        }

        $this->session->flash('uploads.success', 'Upload kaydi silindi.');

        return $this->redirectSuccess('Upload kaydi silindi.', '/admin/uploads');
    }

    private function guard(): ServiceResult
    {
        if ($this->auth->checkAdmin()) {
            return $this->success('Admin oturumu gecerli.');
        }

        return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
    }

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
