<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ApiResponse
{
    /**
     * Return a success response.
     */
    public static function success(mixed $data = [], ?string $message = null, int $code = 200): JsonResponse
    {
        $payload = $data;
        $message ??= __('api.success');

        if ($data instanceof ResourceCollection) {
            $payload = $data->response()->getData(true);
        } elseif ($data instanceof JsonResource) {
            $payload = $data->resolve();
        } elseif (is_array($data) && isset($data['data'])) {
            $payload = $data['data'];
        }

        if (is_array($payload) && isset($payload['data']) && count($payload) === 1) {
            $payload = $payload['data'];
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'payload' => $payload,
        ], $code);
    }

    /**
     * Return a failure response.
     */
    public static function failure(?string $message = null, int $code = 400, mixed $errors = []): JsonResponse
    {
        $message ??= __('api.failure');

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
