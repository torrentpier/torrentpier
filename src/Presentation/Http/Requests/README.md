# HTTP Requests

Request objects and validation:
- Request DTOs with validation rules
- Type-safe access to request data
- File upload handling
- Input sanitization
- Custom validation rules

Example:
```php
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```