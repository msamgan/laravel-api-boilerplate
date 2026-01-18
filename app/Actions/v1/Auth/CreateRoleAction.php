<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use Spatie\Permission\Models\Role;

final readonly class CreateRoleAction
{
    /**
     * @param  array{name: string, permissions: array<string>}  $data
     */
    public function handle(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions']);

        return $role->load('permissions');
    }
}
