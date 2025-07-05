<?php

namespace App\Http\Requests\WordFilter;

use App\Models\WordFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchWordFilterRequest extends FormRequest
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
            'q' => 'required|string|min:1|max:100',
            'limit' => 'sometimes|integer|min:1|max:100',
            'filter_type' => ['sometimes', 'string', Rule::in(WordFilter::getFilterTypes())],
            'severity' => ['sometimes', 'string', Rule::in(WordFilter::getSeverityLevels())],
            'is_active' => 'sometimes|boolean',
            'with_creator' => 'sometimes|boolean',
        ];
    }
}
