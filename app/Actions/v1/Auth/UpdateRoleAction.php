<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final readonly class UpdateRoleAction
{
    /**
     * @param  array{name: string, permissions: array<string>}  $data
     */
    public function handle(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $role->update(['name' => $data['name']]);

            $role->syncPermissions($data['permissions']);

            return $role->load('permissions');
        });
    }
}
