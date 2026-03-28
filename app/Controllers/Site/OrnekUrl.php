<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Services\Site\DynamicRouteService;
use Core\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * Dinamik rota yapisini gostermek icin kullanilan ornek controller.
 */
final class OrnekUrl extends Controller
{
    /**
     * @param View $view Site view goruntuleyicisi.
     * @param DynamicRouteService $dynamicRoute Dinamik rota servis katmani.
     */
    public function __construct(
        View $view,
        private readonly DynamicRouteService $dynamicRoute
    ) {
        parent::__construct($view);
    }

    /**
     * Tek segmentli dinamik rota isteklerini isler.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        $result = $this->dynamicRoute->page($request->path(), self::class, 'index');

        return $this->render('pages/dynamic-example', $result->data());
    }

    /**
     * Iki segmentli dinamik rota isteklerini isler ve sondaki URL segmentlerini alir.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @param string ...$segments Ek dinamik yol segmentleri.
     * @return Response
     */
    public function aboutUs(Request $request, string ...$segments): Response
    {
        $result = $this->dynamicRoute->page($request->path(), self::class, 'aboutUs', $segments);

        return $this->render('pages/dynamic-example', $result->data());
    }
}
