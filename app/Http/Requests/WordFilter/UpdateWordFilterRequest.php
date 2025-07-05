<?php

namespace App\Http\Requests\WordFilter;

use App\Models\WordFilter;
use App\Rules\ValidRegexRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWordFilterRequest extends FormRequest
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
            'pattern' => ['sometimes', 'string', 'max:255', $this->getPatternValidation()],
            'replacement' => $this->getReplacementRules(),
            'filter_type' => ['sometimes', 'string', Rule::in(WordFilter::getFilterTypes())],
            'pattern_type' => ['sometimes', 'string', Rule::in(WordFilter::getPatternTypes())],
            'severity' => ['sometimes', 'string', Rule::in(WordFilter::getSeverityLevels())],
            'is_active' => 'sometimes|boolean',
            'case_sensitive' => 'sometimes|boolean',
            'applies_to' => 'sometimes|array|min:1',
            'applies_to.*' => ['required', 'string', Rule::in(WordFilter::getContentTypes())],
            'created_by' => 'sometimes|nullable|exists:users,id',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get the pattern validation rule based on pattern type.
     */
    protected function getPatternValidation(): ?ValidRegexRule
    {
        $patternType = $this->input('pattern_type') ?? $this->route('filter')->pattern_type;

        if ($patternType === WordFilter::PATTERN_TYPE_REGEX) {
            return new ValidRegexRule;
        }

        return null;
    }

    /**
     * Get the replacement field validation rules.
     */
    protected function getReplacementRules(): array
    {
        $filterType = $this->input('filter_type') ?? $this->route('filter')->filter_type;

        // If the filter type is or will be 'replace'
        if ($filterType === WordFilter::FILTER_TYPE_REPLACE) {
            // If we're changing to replace type, replacement must be provided
            if ($this->has('filter_type') && !$this->filled('replacement')) {
                return ['required', 'string', 'max:255'];
            }

            // Otherwise, validate only if provided
            return ['sometimes', 'required', 'string', 'max:255'];
        }

        // For non-replace types
        return ['sometimes', 'nullable', 'string', 'max:255'];
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
