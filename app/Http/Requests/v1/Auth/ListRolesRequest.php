<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class ListRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @queryParam search string Search term. Example: admin.
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
