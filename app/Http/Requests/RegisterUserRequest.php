<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * User Registration Request Validation
 */
class RegisterUserRequest extends FormRequest
{
    /**
     * Get the validation rules
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:bb_users,username'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:bb_users,user_email'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:72'
            ],
            'password_confirmation' => [
                'required',
                'same:password'
            ],
            'terms' => [
                'accepted'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 25 characters.',
            'username.regex' => 'Username can only contain letters, numbers, hyphens, and underscores.',
            'username.unique' => 'This username is already taken.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.max' => 'Password cannot exceed 72 characters.',
            
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            
            'terms.accepted' => 'You must accept the terms and conditions.'
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'username' => 'username',
            'email' => 'email address',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'terms' => 'terms and conditions'
        ];
    }

    /**
     * Get the username
     */
    public function getUsername(): string
    {
        return str($this->get('username'))->trim()->lower();
    }

    /**
     * Get the email
     */
    public function getEmail(): string
    {
        return str($this->get('email'))->trim()->lower();
    }

    /**
     * Get the password
     */
    public function getPassword(): string
    {
        return $this->get('password');
    }

    /**
     * Check if terms are accepted
     */
    public function hasAcceptedTerms(): bool
    {
        return (bool) $this->get('terms');
    }
}