<?php

declare(strict_types=1);

namespace App\Services\Site;

use App\Services\GlobalTools;
use App\Services\UserAuthService;
use App\Services\UserService;
use Core\Security\Csrf;
use Core\Services\BaseService;
use Core\Services\ServiceResult;

/**
 * Site ana sayfa verisini hazirlar.
 */
final class HomePageService extends BaseService
{
    public function __construct(
        private readonly GlobalTools $globalTools,
        private readonly UserService $users,
        private readonly UserAuthService $userAuth,
        private readonly Csrf $csrf
    ) {
    }

    /**
     * Ana sayfa view verisini uretir.
     *
     * @param string $path Mevcut istek yolu.
     * @return ServiceResult
     */
    public function page(string $path): ServiceResult
    {
        $summary = $this->users->summary()->data();
        $currentUser = $this->userAuth->user();

        return $this->success('Site ana sayfa verisi hazir.', [
            'generated_at' => date('Y-m-d H:i:s'),
            'path' => $path,
            'uploads_path' => $this->globalTools->publicUploadPath('site'),
            'total_users' => (string) ($summary['total_users'] ?? '0'),
            'active_users' => (string) ($summary['active_users'] ?? '0'),
            'latest_user_name' => (string) ($summary['latest_user_name'] ?? '-'),
            'site_auth_html' => $currentUser === null
                ? '<a href="/login" style="display:inline-block; padding:10px 14px; border-radius:12px; background:#1f2937; color:#f9fafb; text-decoration:none;">Giris yap</a>'
                : '<form method="post" action="/logout" style="display:inline-flex; gap:10px; align-items:center; margin:0;"><input type="hidden" name="_token" value="' . htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8') . '"><span>Merhaba, ' . htmlspecialchars((string) ($currentUser['name'] ?? 'Kullanici'), ENT_QUOTES, 'UTF-8') . '</span><button type="submit" style="padding:10px 14px; border-radius:12px; border:none; background:#b91c1c; color:#fff; cursor:pointer;">Cikis yap</button></form>',
        ]);
    }
}
