<?php

declare(strict_types=1);

namespace App\Services;

use Core\Services\ServiceResult;

/**
 * API yanitlari icin sistem ve saglik bilgisi saglar.
 */
final class SystemInfoService
{
    /**
     * @param array $apiConfig Korumali erisim davranisini aciklamak icin kullanilan API yapilandirmasi.
     */
    public function __construct(
        private readonly array $apiConfig = []
    ) {
    }

    /**
     * Herkese acik sistem durum verisini dondurur.
     *
     * @return ServiceResult
     */
    public function status(): ServiceResult
    {
        return ServiceResult::success('System status is ready.', [
            'application' => 'MiU',
            'version' => '0.1.0',
            'server_time' => date('Y-m-d H:i:s'),
            'channels' => [
                'site' => true,
                'admin' => true,
                'api' => true,
            ],
            'features' => [
                'templating' => true,
                'mail_templates' => true,
                'uploads' => true,
                'admin_auth' => true,
                'api_token_auth' => true,
            ],
        ]);
    }

    /**
     * Kimligi dogrulanmis API istemcileri icin korumali sistem durum verisini dondurur.
     *
     * @return ServiceResult
     */
    public function protectedStatus(): ServiceResult
    {
        return ServiceResult::success('Protected API access granted.', [
            'application' => 'MiU',
            'authorized_via' => (string) ($this->apiConfig['header'] ?? 'Authorization'),
            'server_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
