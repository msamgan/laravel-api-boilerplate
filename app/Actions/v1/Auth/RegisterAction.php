<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Enums\Role;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

final readonly class RegisterAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: UserResource, token: string}
     *
     * @throws Throwable
     */
    public function handle(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'uuid' => (string) Str::uuid(),
            ]);

            $user->assignRole(Role::EMPLOYEE->value);

            event(new Registered($user));

            return [
                'user' => new UserResource($user),
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });
    }
}
