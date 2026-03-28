<?php

declare(strict_types=1);

namespace Core;

use Core\Http\ApiResponse;
use Core\Http\Response;
use Core\Services\ServiceResult;
use Core\Validation\FormRequest;
use Core\Validation\ValidationResult;

/**
 * API controller'lari icin public action tanimlamayi standartlastiran temel sinif.
 */
abstract class ApiController extends Controller
{
    /**
     * Token gerektirmeyen action adlarini dondurur.
     *
     * @return array<int, string>
     */
    public static function publicActions(): array
    {
        return [];
    }

    /**
     * Verilen action'in public olarak isaretlenip isaretlenmedigini belirtir.
     *
     * @param string $action Action adi.
     * @return bool
     */
    public static function isPublicAction(string $action): bool
    {
        return in_array($action, static::publicActions(), true);
    }

    /**
     * Servis sonucunu standart API yanitina cevirir.
     *
     * @param ServiceResult $result Servis sonucu.
     * @param int $successStatus Basarili durumda kullanilacak durum kodu.
     * @return Response
     */
    protected function jsonResult(ServiceResult $result, int $successStatus = 200): Response
    {
        return ApiResponse::fromServiceResult($result, $successStatus);
    }

    /**
     * Standart API basari yaniti olusturur.
     *
     * @param string $message Basari mesaji.
     * @param array<string, mixed> $data Sonuc verisi.
     * @param int $status HTTP durum kodu.
     * @return Response
     */
    protected function apiSuccess(string $message, array $data = [], int $status = 200): Response
    {
        return ApiResponse::success($message, $data, $status);
    }

    /**
     * Standart API hata yaniti olusturur.
     *
     * @param string $message Hata mesaji.
     * @param int $status HTTP durum kodu.
     * @param array<string, mixed> $data Ek veri.
     * @return Response
     */
    protected function apiError(string $message, int $status = 422, array $data = []): Response
    {
        return ApiResponse::error($message, $status, $data);
    }

    /**
     * API request nesnesini dogrular.
     *
     * @param FormRequest $request Request sinifi.
     * @param array<string, mixed> $payload Dogrulanacak veri.
     * @return ValidationResult
     */
    protected function validateApiRequest(FormRequest $request, array $payload): ValidationResult
    {
        return $request->validate($payload);
    }

    /**
     * Validation hatalarini standart API yaniti olarak dondurur.
     *
     * @param ValidationResult $validation Dogrulama sonucu.
     * @param string $message Hata mesaji.
     * @return Response
     */
    protected function apiValidationError(ValidationResult $validation, string $message = 'Form verilerini kontrol edin.'): Response
    {
        return ApiResponse::error($message, 422, [
            'errors' => $validation->errors(),
        ]);
    }
}
