<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

final class RegisterController extends Controller
{
    /**
     * Register a new user.
     *
     *
     * @throws Throwable
     */
    public function __invoke(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        return $this->createdResponse(
            $action->handle($request->validated()),
            'User registered'
        );
    }
}
