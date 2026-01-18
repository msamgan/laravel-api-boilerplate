<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @queryParam per_page int Number of items per page. Defaults to 15. Example: 15
     * @queryParam search string Search term. Example: image
     * @queryParam type string Filter by media type. Options: image, document. Example: image
     * @queryParam user_id int Filter by user ID. Example: 1
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(['image', 'document'])],
            'user_id' => ['nullable', 'string', 'exists:users,uuid'],
        ];
    }
}
