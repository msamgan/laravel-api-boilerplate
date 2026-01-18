<?php

declare(strict_types=1);

namespace App\Actions\Permission;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

final readonly class CreatePermissionAction
{
    /**
     * @param  array<string>  $permissions
     *
     * @throws Throwable
     */
    public function handle(array $permissions, ?string $roleName = null): void
    {
        DB::transaction(function () use ($permissions, $roleName): void {
            $guardName = 'web';
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, $guardName);
            }

            $role = Role::findByName($roleName ?? \App\Enums\Role::SUPER_ADMIN->value, $guardName);
            $role->givePermissionTo($permissions);
        });
    }
}
