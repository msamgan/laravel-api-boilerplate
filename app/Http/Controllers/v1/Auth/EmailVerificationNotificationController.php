<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\SendEmailVerificationAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationNotificationController extends Controller
{
    /**
     * Resend email verification link.
     */
    public function __invoke(Request $request, SendEmailVerificationAction $action): JsonResponse
    {
        if ($action->handle($request->user())) {
            return ApiResponse::success(null, 'Verification link sent');
        }

        return ApiResponse::success(null, 'Email already verified');
    }
}
