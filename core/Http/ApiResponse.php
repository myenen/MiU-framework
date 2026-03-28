<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Services\ServiceResult;

/**
 * API yanitlarini tek bir standartta uretir.
 */
final class ApiResponse
{
    /**
     * @param string $message Basari mesaji.
     * @param array<string, mixed> $data Sonuc verisi.
     * @param int $status HTTP durum kodu.
     * @param array<string, mixed> $meta Ek meta verisi.
     * @param array<string, string> $headers Ek header verisi.
     * @return Response
     */
    public static function success(
        string $message,
        array $data = [],
        int $status = 200,
        array $meta = [],
        array $headers = []
    ): Response {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'error' => null,
            'meta' => self::normalizeMeta($meta),
        ], $status, $headers);
    }

    /**
     * @param string $message Hata mesaji.
     * @param int $status HTTP durum kodu.
     * @param array<string, mixed> $data Ek veri.
     * @param array<string, mixed> $error Ek hata verisi.
     * @param array<string, mixed> $meta Ek meta verisi.
     * @param array<string, string> $headers Ek header verisi.
     * @return Response
     */
    public static function error(
        string $message,
        int $status = 422,
        array $data = [],
        array $error = [],
        array $meta = [],
        array $headers = []
    ): Response {
        return Response::json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'error' => array_merge([
                'status' => $status,
            ], $error),
            'meta' => self::normalizeMeta($meta),
        ], $status, $headers);
    }

    /**
     * @param ServiceResult $result Servis sonucu.
     * @param int $successStatus Basarili durumda kullanilacak durum kodu.
     * @param array<string, mixed> $meta Ek meta verisi.
     * @param array<string, string> $headers Ek header verisi.
     * @return Response
     */
    public static function fromServiceResult(
        ServiceResult $result,
        int $successStatus = 200,
        array $meta = [],
        array $headers = []
    ): Response {
        if ($result->isSuccess()) {
            return self::success($result->message(), $result->data(), $successStatus, $meta, $headers);
        }

        return self::error($result->message(), $result->status(), $result->data(), [], $meta, $headers);
    }

    /**
     * @param array<string, mixed> $meta Ek meta verisi.
     * @return array<string, mixed>
     */
    private static function normalizeMeta(array $meta): array
    {
        return array_merge([
            'timestamp' => time(),
        ], $meta);
    }
}
