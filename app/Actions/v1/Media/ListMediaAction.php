<?php

declare(strict_types=1);

namespace App\Actions\v1\Media;

use App\Enums\Role;
use App\Models\Media;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListMediaAction
{
    /**
     * Execute the action.
     *
     * @param  array{search?: string, type?: string, user_id?: int}  $filters
     */
    public function handle(User $authUser, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Media::query()
            ->where('super_admin_id', $authUser->getSuperAdminId());

        if ($authUser->hasRole(Role::CLIENT->value)) {
            $query = $authUser->media();
        }

        return $query->search($filters['search'] ?? null)
            ->byType($filters['type'] ?? null)
            ->when($filters['user_id'] ?? null, function ($query, $userId) {
                $user = User::query()->where('uuid', $userId)->first();

                return $query->whereHas('users', fn ($q) => $q->where('users.id', $user?->id));
            })
            ->with(['user', 'superAdmin', 'client'])
            ->latest()
            ->paginate($perPage);
    }
}
