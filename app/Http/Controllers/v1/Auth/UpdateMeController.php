<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\UpdateMeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\UpdateMeRequest;
use App\Http\Resources\v1\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateMeController extends Controller
{
    /**
     * Update authenticated user profile.
     */
    public function __invoke(UpdateMeRequest $request, UpdateMeAction $action): JsonResponse
    {
        $user = $action->handle($request->user(), $request->validated())->load('profilePicture');

        return ApiResponse::success(
            (new UserResource($user))->withPermissions(),
            'User information updated successfully'
        );
    }
}
