<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListUsersAction
{
    /**
     * Execute the action.
     */
    public function handle(array $data): LengthAwarePaginator
    {
        return User::query()
            ->with(['creator'])
            ->search($data['search'] ?? null)
            ->filterByRole($data['role'] ?? null)
            ->filterByActive($data['is_active'] ?? null)
            ->latest()
            ->paginate();
    }
}
