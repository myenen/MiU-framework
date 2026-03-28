<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\AuthService;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;

/**
 * Admin API dokumantasyon sayfasinin servis katmanini uretir.
 */
final class ApiDocsPageService extends BasePageService
{
    public function __construct(
        private readonly AuthService $auth
    ) {
    }

    public function page(string $path): ServiceResult
    {
        if (! $this->auth->checkAdmin()) {
            return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
        }

        return $this->success('API dokumantasyon sayfasi hazir.', [
            'path' => $path,
            'api_header' => 'X-Api-Token',
        ]);
    }
}
