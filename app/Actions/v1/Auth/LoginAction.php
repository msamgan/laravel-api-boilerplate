<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class LoginAction
{
    /**
     * @param  array{email: string, password: string}  $data
     * @return array{user: UserResource, token: string}
     *
     * @throws ValidationException
     */
    public function handle(array $data): array
    {
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        $user = $user->load(['profilePicture']);

        return [
            'user' => (new UserResource($user))->withPermissions(),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }
}
