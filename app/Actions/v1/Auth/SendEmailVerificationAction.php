<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use App\Models\User;

final readonly class SendEmailVerificationAction
{
    public function handle(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}
