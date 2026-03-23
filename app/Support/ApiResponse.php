<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Standard JSON envelope for API responses (success and error).
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|object|null  $data
     */
    public static function success(array|object|null $data = null, string $message = 'Успешно выполнено', int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(int $code, string $message, ?array $errors = null, int $httpStatus = 400): JsonResponse
    {
        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($errors !== null) {
            $payload['error']['errors'] = $errors;
        }

        return response()->json($payload, $httpStatus);
    }
}
