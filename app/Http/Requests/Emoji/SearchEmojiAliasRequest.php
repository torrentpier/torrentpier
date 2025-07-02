<?php

namespace App\Http\Requests\Emoji;

use Illuminate\Foundation\Http\FormRequest;

class SearchEmojiAliasRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => 'required|string|min:1',
            'limit' => 'integer|min:1|max:100',
        ];
    }
}
