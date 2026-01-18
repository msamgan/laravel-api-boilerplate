<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ChangePasswordRequest extends FormRequest
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
     * @bodyParam current_password string required The current password of the user. Example: old-password
     * @bodyParam password string required The new password. Example: new-password
     * @bodyParam password_confirmation string required The new password confirmation. Example: new-password
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }
}
