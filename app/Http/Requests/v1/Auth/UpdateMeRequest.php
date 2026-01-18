<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMeRequest extends FormRequest
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
     * @bodyParam profile_picture file The profile picture of the user.
     * @bodyParam remove_profile_picture boolean If true, the profile picture will be removed.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'string',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'profile_picture' => ['sometimes', 'image', 'max:2048'],
            'remove_profile_picture' => ['sometimes', 'boolean'],
        ];
    }
}
