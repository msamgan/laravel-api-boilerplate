<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\ChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;

final class ChangePasswordController extends Controller
{
    /**
     * Change authenticated user password.
     */
    public function __invoke(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->handle($request->user(), $request->validated());

        return $this->successResponse(null, 'Password changed successfully');
    }
}
