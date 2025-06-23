<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Base Form Request class for validation
 */
abstract class FormRequest
{
    protected Request $request;
    protected array $validated = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->runValidation();
    }

    /**
     * Get the validation rules that apply to the request
     */
    abstract public function rules(): array;

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * Get specific validated field
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->validated, $key, $default);
    }

    /**
     * Get all request data
     */
    public function all(): array
    {
        return $this->request->all();
    }

    /**
     * Get only specific fields from request
     */
    public function only(array $keys): array
    {
        return $this->request->only($keys);
    }

    /**
     * Get request data except specific fields
     */
    public function except(array $keys): array
    {
        return $this->request->except($keys);
    }

    /**
     * Run the validation
     */
    protected function runValidation(): void
    {
        if (!$this->authorize()) {
            throw new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
        }

        $validator = Validator::make(
            $this->request->all(),
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->validated = $validator->validated();
    }
}