<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
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
     * @bodyParam name string The name of the user. Example: John Doe
     * @bodyParam email string The email of the user. Example: john@example.com
     * @bodyParam role string The role of the user. Example: Employee
     * @bodyParam is_active boolean The active status of the user. Example: true
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->getKey() : $user;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($userId), 'max:255'],
            'role' => ['sometimes', 'required', 'string', Rule::exists('roles', 'name')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
