<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final readonly class ForgotPasswordAction
{
    /**
     * @param  array{email: string}  $data
     *
     * @throws ValidationException
     */
    public function handle(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
