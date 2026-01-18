<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @bodyParam token string required The password reset token. Example: some-random-token
     * @bodyParam email string required The email of the user. Example: user@example.com
     * @bodyParam password string required The new password. Example: new-password
     * @bodyParam password_confirmation string required The password confirmation. Example: new-password
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
