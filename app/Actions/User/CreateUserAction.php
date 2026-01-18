<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final readonly class CreateUserAction
{
    /**
     * Execute the action.
     *
     * @throws Throwable
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'uuid' => Str::uuid(),
                'is_active' => $data['is_active'] ?? true,
                'profile_picture_id' => null,
                'email_verified_at' => now(),
            ]);

            $roleName = $data['role'];
            $roleModel = config('permission.models.role');

            if (! $roleModel::query()->where('name', $roleName)->exists()) {
                throw new InvalidArgumentException("Role [{$roleName}] does not exist.");
            }

            $user->assignRole($roleName);

            return $user;
        });
    }
}
