<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\SystemInfoService;
use Core\ApiController;
use Core\Http\Request;
use Core\Http\Response;
use Core\View\View;

/**
 * API istemcileri icin temel saglik ve korumali durum endpoint'lerini sunar.
 */
final class StatusController extends ApiController
{
    public static function publicActions(): array
    {
        return ['index'];
    }

    /**
     * @param View $view Controller otomatik baglama tutarliligi icin tutulan view bagimliligi.
     * @param SystemInfoService $systemInfo Sistem durum verisini donduren servis.
     */
    public function __construct(
        View $view,
        private readonly SystemInfoService $systemInfo
    ) {
        parent::__construct($view);
    }

    /**
     * Herkese acik sistem bilgisini dondurur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->jsonResult($this->systemInfo->status());
    }

    /**
     * API token dogrulamasindan sonra korumali sistem bilgisini dondurur.
     *
     * @param Request $request Mevcut HTTP istegi.
     * @return Response
     */
    public function secure(Request $request): Response
    {
        return $this->jsonResult($this->systemInfo->protectedStatus());
    }
}
