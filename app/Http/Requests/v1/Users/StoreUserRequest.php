<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserRequest extends FormRequest
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
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password
     * @bodyParam password_confirmation string required The password confirmation of the user. Example: password
     * @bodyParam role string required The role of the user. Example: Employee
     * @bodyParam is_active boolean The active status of the user. Example: true
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
