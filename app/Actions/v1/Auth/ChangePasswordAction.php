<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class ChangePasswordAction
{
    /**
     * @param  array{current_password: string, password: string}  $data
     */
    public function handle(User $user, array $data): void
    {
        $user->update(['password' => Hash::make($data['password'])]);
    }
}
