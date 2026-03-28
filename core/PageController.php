<?php

declare(strict_types=1);

namespace Core;

use Core\Http\Response;
use Core\Services\ServiceResult;

/**
 * Sayfa odakli controller'lar icin ortak render ve redirect yardimcilari sunar.
 */
abstract class PageController extends Controller
{
    /**
     * Servis sonucunu sayfa render akisina cevirir.
     *
     * @param ServiceResult $result Sayfa servisi sonucu.
     * @param string $template Render edilecek view.
     * @param string|null $layout Opsiyonel layout.
     * @param callable|null $transform Render oncesi veri donusumu.
     * @param string $defaultRedirect Hata halinde varsayilan yonlendirme.
     * @return Response
     */
    protected function renderPageResult(
        ServiceResult $result,
        string $template,
        ?string $layout = null,
        ?callable $transform = null,
        string $defaultRedirect = '/'
    ): Response {
        $redirectTo = (string) ($result->data()['redirect_to'] ?? '');

        if ($redirectTo !== '') {
            return $this->redirect($redirectTo, $this->resolveRedirectStatus($result->status()));
        }

        if (! $result->isSuccess()) {
            return $this->redirect($defaultRedirect, $this->resolveRedirectStatus($result->status()));
        }

        $data = $result->data();

        if ($transform !== null) {
            $data = $transform($data);
        }

        return $this->render($template, $data, $layout);
    }

    /**
     * Servis sonucunu yalnizca yonlendirme yanitina cevirir.
     *
     * @param ServiceResult $result Islem sonucu.
     * @param string $defaultRedirect Sonuc veri setinde hedef yoksa kullanilacak yol.
     * @return Response
     */
    protected function redirectResult(ServiceResult $result, string $defaultRedirect): Response
    {
        return $this->redirect(
            (string) ($result->data()['redirect_to'] ?? $defaultRedirect),
            $this->resolveRedirectStatus($result->status())
        );
    }

    /**
     * Gecersiz durum kodlarini guvenli redirect koduna indirger.
     *
     * @param int $status Servis sonucu durum kodu.
     * @return int
     */
    private function resolveRedirectStatus(int $status): int
    {
        return in_array($status, [301, 302, 303, 307, 308], true) ? $status : 302;
    }
}
