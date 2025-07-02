<?php

namespace App\Http\Requests\Emoji;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmojiCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add proper authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'display_order' => 'sometimes|integer|min:0',
        ];
    }
}
