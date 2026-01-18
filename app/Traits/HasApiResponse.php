<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

trait HasApiResponse
{
    /**
     * Return a created success response.
     */
    protected function createdResponse(mixed $data, ?string $modelName = null): JsonResponse
    {
        $message = $modelName
            ? __(':model created successfully', ['model' => ucfirst($modelName)])
            : __('api.created');

        return ApiResponse::success($data, $message, 201);
    }

    /**
     * Return an updated success response.
     */
    protected function updatedResponse(mixed $data, ?string $modelName = null): JsonResponse
    {
        $message = $modelName
            ? __(':model updated successfully', ['model' => ucfirst($modelName)])
            : __('api.updated');

        return ApiResponse::success($data, $message);
    }

    /**
     * Return a deleted success response.
     */
    protected function deletedResponse(?string $modelName = null): JsonResponse
    {
        $message = $modelName
            ? __(':model deleted successfully', ['model' => ucfirst($modelName)])
            : __('api.deleted');

        return ApiResponse::success(null, $message);
    }

    /**
     * Return a success response.
     */
    protected function successResponse(mixed $data = [], ?string $message = null): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }
}
