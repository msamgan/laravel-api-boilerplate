<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\VerifyEmailAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

final class VerifyEmailController extends Controller
{
    /**
     * Verify user email.
     */
    public function __invoke(EmailVerificationRequest $request, VerifyEmailAction $action): JsonResponse
    {
        if ($action->handle($request->user())) {
            return ApiResponse::success(null, 'Email verified successfully');
        }

        return ApiResponse::success(null, 'Email already verified');
    }
}
