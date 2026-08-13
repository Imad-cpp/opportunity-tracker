<?php

namespace App\Rules;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;

class IanaTimezone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute field must be a valid IANA time zone.');

            return;
        }

        $identifiers = array_merge(['UTC'], DateTimeZone::listIdentifiers());

        if (! in_array($value, $identifiers, true)) {
            $fail('The :attribute field must be a valid IANA time zone.');
        }
    }
}
