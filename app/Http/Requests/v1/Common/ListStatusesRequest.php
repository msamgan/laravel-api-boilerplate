<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Common;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ListStatusesRequest extends FormRequest
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
     * @queryParam search string Search term to filter statuses by label or value. Example: new
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model' => ['required', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'model' => $this->route('model'),
        ]);
    }
}
