<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\ForgotPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\ForgotPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class ForgotPasswordController extends Controller
{
    /**
     * Send password reset link.
     */
    public function __invoke(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        try {
            return ApiResponse::success(message: $action->handle($request->validated()));
        } catch (ValidationException $e) {
            return ApiResponse::failure($e->getMessage(), 422, $e->errors());
        }
    }
}
