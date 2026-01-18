<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Permission\CreatePermissionAction;
use App\Actions\User\CreateUserAction;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Throwable;

final class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws Throwable
     */
    public function run(): void
    {
        Role::findOrCreate(RoleEnum::SUPER_ADMIN->value, 'web');
        Role::findOrCreate(RoleEnum::EMPLOYEE->value, 'web');
        Role::findOrCreate(RoleEnum::CLIENT->value, 'web');

        if (! User::query()->where('email', 'm.samgan@mail.com')->exists()) {
            app(CreateUserAction::class)->handle([
                'name' => 'Mohammed Samgan Khan',
                'email' => 'm.samgan@mail.com',
                'password' => 'wUyrAjEYytz8$MphaGJJ8=#*9&DBbQn',
                'role' => RoleEnum::SUPER_ADMIN->value,
                'email_verified_at' => now(),
            ]);
        }

        $permissions = [
            'media.view',
            'media.create',
            'media.delete',
            'permissions.view',
            'changelog.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ];

        app(CreatePermissionAction::class)->handle($permissions);

        app(CreatePermissionAction::class)->handle(['media.view'], RoleEnum::CLIENT->value);
    }
}
