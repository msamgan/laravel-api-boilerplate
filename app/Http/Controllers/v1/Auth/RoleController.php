<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Actions\v1\Auth\CreateRoleAction;
use App\Actions\v1\Auth\UpdateRoleAction;
use App\Actions\v1\Common\DeleteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\ListRolesRequest;
use App\Http\Requests\v1\Auth\StoreRoleRequest;
use App\Http\Requests\v1\Auth\UpdateRoleRequest;
use App\Http\Resources\v1\Auth\RoleResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

final class RoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all available roles along with their permissions.
     */
    public function index(ListRolesRequest $request): JsonResponse
    {
        $this->authorize('roles.view');

        $roles = Role::query()
            ->with('permissions')
            ->when($request->search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return $this->successResponse(RoleResource::collection($roles)->resolve());
    }

    /**
     * Create a new role.
     */
    public function store(StoreRoleRequest $request, CreateRoleAction $action): JsonResponse
    {
        $this->authorize('roles.create');

        $role = $action->handle($request->validated());

        return $this->createdResponse(new RoleResource($role), 'Role');
    }

    /**
     * Update an existing role.
     */
    public function update(UpdateRoleRequest $request, Role $role, UpdateRoleAction $action): JsonResponse
    {
        $this->authorize('roles.update');

        $role = $action->handle($role, $request->validated());

        return $this->updatedResponse(new RoleResource($role), 'Role');
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role, DeleteAction $action): JsonResponse
    {
        $this->authorize('roles.delete');

        $action->handle($role);

        return $this->deletedResponse('Role');
    }
}
