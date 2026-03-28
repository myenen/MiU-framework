<?php

declare(strict_types=1);

namespace App\Services\Site;

use Core\Services\BaseService;
use Core\Services\ServiceResult;

/**
 * Dinamik site rota ekranlari icin view verisini hazirlar.
 */
final class DynamicRouteService extends BaseService
{
    /**
     * Dinamik rota ornek sayfasi icin view verisini dondurur.
     *
     * @param string $path Mevcut istek yolu.
     * @param string $className Controller sinif adi.
     * @param string $methodName Calisan metod adi.
     * @param array<int, string> $segments Ek URL segmentleri.
     * @return ServiceResult
     */
    public function page(string $path, string $className, string $methodName, array $segments = []): ServiceResult
    {
        return $this->success('Dinamik rota verisi hazir.', [
            'app_name' => 'MiU',
            'path' => $path,
            'class_name' => $className,
            'method_name' => $methodName,
            'extra_segments' => $segments !== [] ? implode(', ', $segments) : '-',
        ]);
    }
}
