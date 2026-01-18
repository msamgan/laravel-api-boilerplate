<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Common;

use App\Actions\v1\Common\ToggleActiveAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Common\ToggleActiveRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class ToggleActiveController extends Controller
{
    private array $modelMap = [
        'user' => User::class,
        // Add other models here as needed
    ];

    private array $permissionMap = [
        'user' => 'users.update',
    ];

    /**
     * Toggle the active status of a model.
     */
    public function __invoke(ToggleActiveRequest $request, ToggleActiveAction $action): JsonResponse
    {
        $modelName = mb_strtolower((string) $request->validated('model'));

        Gate::authorize($this->permissionMap[$modelName]);

        $modelClass = $this->modelMap[$modelName];
        $id = $request->validated('id');

        $model = (new $modelClass)->getRouteKeyName() === 'id'
            ? $modelClass::findOrFail($id)
            : $modelClass::where((new $modelClass)->getRouteKeyName(), $id)->firstOrFail();

        $action->handle($model);

        return $this->successResponse(
            null,
            Str::headline($request->validated('model')) . ' status toggled successfully'
        );
    }
}
