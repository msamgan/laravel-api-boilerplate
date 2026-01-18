<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

final readonly class VerifyEmailAction
{
    public function handle(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return true;
    }
}
