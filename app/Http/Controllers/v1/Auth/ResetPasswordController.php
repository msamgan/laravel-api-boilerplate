<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class ResetPasswordController extends Controller
{
    /**
     * Reset password.
     */
    public function __invoke(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        try {
            $action->handle($request->validated());

            return ApiResponse::success(message: 'Password reset successful');
        } catch (ValidationException $e) {
            return ApiResponse::failure($e->getMessage(), 422, $e->errors());
        }
    }
}
