<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Services\Site\HomePageService;
use Core\Http\Request;
use Core\Http\Response;
use Core\SiteController;
use Core\View\RawValue;
use Core\View\View;

/**
 * Genel site ana sayfasini goruntuler.
 */
final class HomeController extends SiteController
{
    /**
     * @param View $view Site view goruntuleyicisi.
     * @param HomePageService $homePage Site ana sayfa servis katmani.
     */
    public function __construct(
        View $view,
        private readonly HomePageService $homePage
    ) {
        parent::__construct($view);
    }

    /**
     * Site acilis sayfasini gosterir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        $result = $this->homePage->page($request->path());
        $data = $result->data();
        $data['site_auth_html'] = new RawValue((string) ($data['site_auth_html'] ?? ''));

        return $this->render('pages/home', $data);
    }
}
