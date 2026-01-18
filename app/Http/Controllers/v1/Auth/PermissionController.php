<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\ListPermissionsRequest;
use App\Http\Resources\v1\Auth\PermissionResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

final class PermissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all available permissions.
     */
    public function index(ListPermissionsRequest $request): JsonResponse
    {
        $this->authorize('permissions.view');

        $permissions = Permission::query()
            ->when($request->search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return $this->successResponse(PermissionResource::collection($permissions)->resolve());
    }
}
