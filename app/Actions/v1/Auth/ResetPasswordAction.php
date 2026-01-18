<?php

declare(strict_types=1);

namespace App\Actions\v1\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class ResetPasswordAction
{
    /**
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $data
     *
     * @throws ValidationException
     */
    public function handle(array $data): void
    {
        $status = Password::broker()->reset(
            $data,
            function ($user, $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
