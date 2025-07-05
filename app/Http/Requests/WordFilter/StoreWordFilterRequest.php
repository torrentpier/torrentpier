<?php

namespace App\Http\Requests\WordFilter;

use App\Models\WordFilter;
use App\Rules\ValidRegexRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWordFilterRequest extends FormRequest
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
            'pattern' => ['required', 'string', 'max:255', $this->getPatternValidation()],
            'replacement' => ['nullable', 'string', 'max:255', $this->getReplacementValidation()],
            'filter_type' => ['required', 'string', Rule::in(WordFilter::getFilterTypes())],
            'pattern_type' => ['required', 'string', Rule::in(WordFilter::getPatternTypes())],
            'severity' => ['required', 'string', Rule::in(WordFilter::getSeverityLevels())],
            'is_active' => 'sometimes|boolean',
            'case_sensitive' => 'sometimes|boolean',
            'applies_to' => 'required|array|min:1',
            'applies_to.*' => ['required', 'string', Rule::in(WordFilter::getContentTypes())],
            'created_by' => 'sometimes|nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the pattern validation rule based on pattern type.
     */
    protected function getPatternValidation(): ?ValidRegexRule
    {
        if ($this->input('pattern_type') === WordFilter::PATTERN_TYPE_REGEX) {
            return new ValidRegexRule;
        }

        return null;
    }

    /**
     * Get the replacement validation rule based on filter type.
     */
    protected function getReplacementValidation(): ?string
    {
        if ($this->input('filter_type') === WordFilter::FILTER_TYPE_REPLACE) {
            return 'required';
        }

        return null;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'replacement.required' => 'The replacement field is required when filter type is replace.',
            'applies_to.required' => 'At least one content type must be selected.',
            'applies_to.*.in' => 'Invalid content type selected.',
        ];
    }
}
