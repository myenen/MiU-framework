<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\ApiDocsPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * Admin dokumantasyon sayfalarini yonetir.
 */
final class Docs extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'api' => 'docs.view',
        ];
    }

    public function __construct(
        View $view,
        private readonly ApiDocsPageService $docs
    ) {
        parent::__construct($view);
    }

    public function api(Request $request): Response
    {
        return $this->renderPageResult($this->docs->page($request->path()), 'pages/docs/api', null, null, '/admin/login');
    }
}
