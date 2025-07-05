<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRegexRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a valid regex pattern.');

            return;
        }

        // Set error handler to catch warnings
        $isValid = true;
        set_error_handler(function ($errno, $errstr) use (&$isValid) {
            $isValid = false;

            return true; // Suppress the warning
        }, E_WARNING);

        // Try to use the pattern
        preg_match($value, '');

        // Restore previous error handler
        restore_error_handler();

        // Check if there was an error
        if (!$isValid || preg_last_error() !== PREG_NO_ERROR) {
            $fail('The :attribute must be a valid regex pattern.');
        }
    }
}
