<?php

declare(strict_types=1);

namespace Core;

use Core\Http\Response;
use Core\Services\ServiceResult;
use Core\View\View;

/**
 * HTML, yonlendirme ve JSON yanitlari icin kolaylastirici yardimcilari olan temel controller.
 */
abstract class Controller
{
    /**
     * Action bazli gerekli yetki listesini dondurur.
     *
     * @return array<string, string|array<int, string>>
     */
    public static function actionPermissions(): array
    {
        return [];
    }

    /**
     * Verilen action icin gerekli yetki listesini dondurur.
     *
     * @param string $action Action adi.
     * @return array<int, string>
     */
    public static function permissionsForAction(string $action): array
    {
        $map = static::actionPermissions();
        $value = $map[$action] ?? [];
        $permissions = is_array($value) ? $value : [$value];

        return array_values(array_filter(array_map(
            static fn (string $permission): string => trim($permission),
            $permissions
        ), static fn (string $permission): bool => $permission !== ''));
    }

    /**
     * @param View $view Controller alanina atanmis view goruntuleyicisi.
     */
    public function __construct(
        protected View $view
    ) {
    }

    /**
     * Bir HTML sablonunu render eder ve Response nesnesine sarar.
     *
     * @param string $template View adi.
     * @param array $data View verisi.
     * @param string|null $layout Opsiyonel layout ezmesi.
     * @return Response
     */
    protected function render(string $template, array $data = [], ?string $layout = null): Response
    {
        return Response::html($this->view->render($template, $data, $layout));
    }

    /**
     * HTTP yonlendirme yaniti olusturur.
     *
     * @param string $location Hedef URL ya da yol.
     * @param int $status Yonlendirme durum kodu.
     * @return Response
     */
    protected function redirect(string $location, int $status = 302): Response
    {
        return Response::redirect($location, $status);
    }

    /**
     * JSON yaniti olusturur.
     *
     * @param array $data JSON verisi.
     * @param int $status HTTP durum kodu.
     * @return Response
     */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    /**
     * Servis sonuc nesnesini framework'un JSON yanit bicimine cevirir.
     *
     * @param ServiceResult $result Yapilandirilmis servis sonucu.
     * @param int $successStatus Sonuc basarili oldugunda kullanilacak HTTP durum kodu.
     * @return Response
     */
    protected function jsonResult(ServiceResult $result, int $successStatus = 200): Response
    {
        return $this->json($result->toArray(), $result->isSuccess() ? $successStatus : $result->status());
    }
}
