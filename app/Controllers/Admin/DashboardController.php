<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\DashboardPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * Korumali admin panelini goruntuler.
 */
final class DashboardController extends AdminController
{
    public static function actionPermissions(): array
    {
        return [
            'index' => 'dashboard.view',
        ];
    }

    /**
     * @param View $view Admin view goruntuleyicisi.
     * @param DashboardPageService $dashboardPage Dashboard servis katmani.
     */
    public function __construct(
        View $view,
        private readonly DashboardPageService $dashboardPage
    ) {
        parent::__construct($view);
    }

    /**
     * Dogrulanmis admin kullanicilari icin paneli gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->renderPageResult($this->dashboardPage->page($request->path()), 'pages/dashboard', null, null, '/admin/login');
    }
}
