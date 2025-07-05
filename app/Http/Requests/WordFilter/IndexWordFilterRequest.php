<?php

namespace App\Http\Requests\WordFilter;

use App\Models\WordFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexWordFilterRequest extends FormRequest
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
            'filter_type' => ['sometimes', 'string', Rule::in(WordFilter::getFilterTypes())],
            'pattern_type' => ['sometimes', 'string', Rule::in(WordFilter::getPatternTypes())],
            'severity' => ['sometimes', 'string', Rule::in(WordFilter::getSeverityLevels())],
            'is_active' => 'sometimes|boolean',
            'applies_to' => ['sometimes', 'string', Rule::in(WordFilter::getContentTypes())],
            'search' => 'sometimes|string|max:100',
            'with_creator' => 'sometimes|boolean',
            'sort_by' => ['sometimes', 'string', Rule::in(['created_at', 'updated_at', 'pattern', 'severity'])],
            'sort_order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
