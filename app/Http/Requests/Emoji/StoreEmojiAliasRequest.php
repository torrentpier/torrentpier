<?php

namespace App\Http\Requests\Emoji;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmojiAliasRequest extends FormRequest
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
            'emoji_id' => 'required|exists:emojis,id',
            'alias' => [
                'required',
                'string',
                'max:255',
                'regex:/^:[a-zA-Z0-9_-]+:$/',
                'unique:emoji_aliases,alias',
                Rule::notIn(\App\Models\Emoji::pluck('emoji_shortcode')->toArray()),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'alias.regex' => 'The alias must be in the format :name: (e.g., :happy:)',
            'alias.unique' => 'This alias is already taken.',
            'alias.not_in' => 'This alias conflicts with an existing emoji shortcode.',
        ];
    }
}
