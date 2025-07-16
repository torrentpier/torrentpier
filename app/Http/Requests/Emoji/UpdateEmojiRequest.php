<?php

namespace App\Http\Requests\Emoji;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmojiRequest extends FormRequest
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
        $emojiId = $this->route('emoji')->id;

        return [
            'title' => 'sometimes|string|max:255',
            'emoji_text' => 'nullable|string|max:10',
            'emoji_shortcode' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^:[a-zA-Z0-9_-]+:$/',
                Rule::unique('emojis', 'emoji_shortcode')->ignore($emojiId),
                Rule::notIn(\App\Models\EmojiAlias::pluck('alias')->toArray()),
            ],
            'image' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,gif,webp',
            'image_url' => 'nullable|string|max:500',
            'sprite_mode' => 'sometimes|boolean',
            'sprite_params' => 'nullable|array',
            'sprite_params.x' => 'required_if:sprite_mode,true|integer|min:0',
            'sprite_params.y' => 'required_if:sprite_mode,true|integer|min:0',
            'sprite_params.width' => 'required_if:sprite_mode,true|integer|min:1',
            'sprite_params.height' => 'required_if:sprite_mode,true|integer|min:1',
            'sprite_params.sheet' => 'required_if:sprite_mode,true|string|max:255',
            'emoji_category_id' => 'nullable|exists:emoji_categories,id',
            'display_order' => 'sometimes|integer|min:0',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'emoji_shortcode.regex' => 'The emoji shortcode must be in the format :name: (e.g., :smile:)',
            'emoji_shortcode.unique' => 'This shortcode is already taken.',
            'emoji_shortcode.not_in' => 'This shortcode conflicts with an existing alias.',
            'image.max' => 'The image must not be larger than 2MB.',
        ];
    }
}
