<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class LoginController extends Controller
{
    /**
     * Log in a user.
     */
    public function __invoke(LoginRequest $request, LoginAction $action): JsonResponse
    {
        try {
            return $this->successResponse($action->handle($request->validated()), 'Login successful');
        } catch (ValidationException $e) {
            return ApiResponse::failure($e->getMessage(), 422, $e->errors());
        }
    }
}
