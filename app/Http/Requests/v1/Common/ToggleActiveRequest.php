<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ToggleActiveRequest extends FormRequest
{
    /**
     * @bodyParam model string required The model to toggle. Example: user
     * @bodyParam id string required The ID of the model instance. Example: 1
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'model' => ['required', 'string', Rule::in(['user'])],
            'id' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
