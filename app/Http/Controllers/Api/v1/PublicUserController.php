<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PublicUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class PublicUserController extends Controller
{
    /**
     * Get minimal user info (public).
     *
     * @endpoint Public User Info
     */
    public function __invoke(User $user): JsonResponse
    {
        $user->load(['profilePicture']);

        return $this->successResponse(
            data: new PublicUserResource($user),
            message: 'User information retrieved successfully.'
        );
    }
}
