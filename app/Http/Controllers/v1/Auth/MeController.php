<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    /**
     * Get authenticated user profile.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->load(['profilePicture']);

        return $this->successResponse(new UserResource($request->user()));
    }
}
