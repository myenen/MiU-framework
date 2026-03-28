<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\AuthService;
use App\Services\GlobalTools;
use App\Services\UserService;
use Core\Cache\FileCache;
use Core\Security\Csrf;
use Core\Services\BaseService;
use Core\Services\ServiceResult;

/**
 * Admin panel anasayfa verisini ve erisim kurallarini yonetir.
 */
final class DashboardPageService extends BaseService
{
    /**
     * @param FileCache $cache Panel istatistik cache servisi.
     * @param GlobalTools $globalTools Ortak yardimci servisler.
     * @param AuthService $auth Admin kimlik dogrulama servisi.
     * @param Csrf $csrf Form token servisi.
     */
    public function __construct(
        private readonly FileCache $cache,
        private readonly GlobalTools $globalTools,
        private readonly AuthService $auth,
        private readonly Csrf $csrf,
        private readonly UserService $users
    ) {
    }

    /**
     * Dashboard ekrani icin gerekli veriyi uretir.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function page(string $path): ServiceResult
    {
        if (! $this->auth->checkAdmin()) {
            return $this->error('Admin oturumu bulunamadi.', [
                'redirect_to' => '/admin/login',
            ], 401);
        }

        $admin = $this->auth->admin();
        $summary = $this->users->summary()->data();
        $stats = $this->cache->remember('admin.dashboard.stats', static function (): array {
            return [
                'orders' => 42,
                'tickets' => 7,
            ];
        }, 120);

        return $this->success('Dashboard verisi hazir.', [
            'generated_at' => date('Y-m-d H:i:s'),
            'path' => $path,
            'users_count' => (string) ($summary['total_users'] ?? '0'),
            'orders_count' => (string) $stats['orders'],
            'tickets_count' => (string) $stats['tickets'],
            'uploads_path' => $this->globalTools->publicUploadPath('admin'),
            'admin_name' => (string) ($admin['name'] ?? 'Admin'),
            'csrf_token' => $this->csrf->token(),
            'active_users_count' => (string) ($summary['active_users'] ?? '0'),
            'latest_user_name' => (string) ($summary['latest_user_name'] ?? '-'),
        ]);
    }
}
