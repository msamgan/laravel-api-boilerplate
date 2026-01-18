<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Users;

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
     * @bodyParam password string required The new password. Example: new-password
     * @bodyParam password_confirmation string required The new password confirmation. Example: new-password
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
