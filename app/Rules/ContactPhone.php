<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContactPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Accept local and international formatting without assuming a country.
        $digits = preg_replace('/\D/', '', (string) $value);
        if (! preg_match('/^\+?[0-9\s().-]+$/', (string) $value)
            || strlen($digits) < 7 || strlen($digits) > 15) {
            $fail('validation.contact_phone')->translate();
        }
    }
}
