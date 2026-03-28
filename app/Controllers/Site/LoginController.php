<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Services\Site\SiteLoginPageService;
use Core\Http\Request;
use Core\Http\Response;
use Core\SiteController;
use Core\View\View;

/**
 * Site kullanici giris ve cikis akisini yonetir.
 */
final class LoginController extends SiteController
{
    /**
     * @param View $view Site view goruntuleyicisi.
     * @param SiteLoginPageService $loginPage Site login servis katmani.
     */
    public function __construct(
        View $view,
        private readonly SiteLoginPageService $loginPage
    ) {
        parent::__construct($view);
    }

    /**
     * Site login formunu gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function show(Request $request): Response
    {
        return $this->renderPageResult($this->loginPage->form($request->path()), 'pages/login', null, null, '/login');
    }

    /**
     * Site kullanici girisini isler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function login(Request $request): Response
    {
        return $this->redirectResult($this->loginPage->login($request->all()), '/login');
    }

    /**
     * Site kullanici oturumunu kapatir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function logout(Request $request): Response
    {
        return $this->redirectResult($this->loginPage->logout(), '/login');
    }
}
