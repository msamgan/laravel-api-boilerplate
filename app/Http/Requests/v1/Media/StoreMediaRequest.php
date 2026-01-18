<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\Media;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaRequest extends FormRequest
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
     * @bodyParam file required The file to upload. Max 10MB. Allowed types: jpg, jpeg, png, gif, bmp, svg, webp, pdf.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf',
            ],
            'user_id' => [
                'nullable',
                'string',
                'exists:users,uuid',
            ],
        ];
    }
}
