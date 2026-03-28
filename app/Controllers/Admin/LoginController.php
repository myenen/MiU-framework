<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\AdminLoginPageService;
use Core\AdminController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * Admin giris ve cikis islemlerini yonetir.
 */
final class LoginController extends AdminController
{
    /**
     * @param View $view Admin kimlik dogrulama view goruntuleyicisi.
     * @param AdminLoginPageService $loginPage Admin giris servis katmani.
     */
    public function __construct(
        View $view,
        private readonly AdminLoginPageService $loginPage
    ) {
        parent::__construct($view);
    }

    /**
     * Admin giris formunu gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function show(Request $request): Response
    {
        return $this->renderPageResult($this->loginPage->form($request->path()), 'pages/login', 'layouts/auth', null, '/admin/login');
    }

    /**
     * Gonderilen bilgileri dogrular ve bir admin oturumu olusturur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function login(Request $request): Response
    {
        return $this->redirectResult($this->loginPage->login($request->all()), '/admin/login');
    }

    /**
     * Mevcut admin kullanicisinin oturumunu kapatir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function logout(Request $request): Response
    {
        return $this->redirectResult($this->loginPage->logout(), '/admin/login');
    }
}
