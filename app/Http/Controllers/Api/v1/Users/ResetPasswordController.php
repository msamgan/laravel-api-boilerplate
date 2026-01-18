<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Users\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Group;

#[Group('Users')]
final class ResetPasswordController extends Controller
{
    use AuthorizesRequests;

    /**
     * Reset the user's password.
     */
    public function __invoke(ResetPasswordRequest $request, User $user): JsonResponse
    {
        $this->authorize('users.update');

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return ApiResponse::success(null, __('Password reset successfully'));
    }
}
