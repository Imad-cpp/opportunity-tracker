<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\IanaTimezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'timezone' => ['required', 'string', 'max:64', new IanaTimezone],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $name = $this->input('name');
        $timezone = $this->input('timezone');

        $this->merge([
            'email' => is_string($email) ? Str::lower(trim($email)) : $email,
            'name' => is_string($name) ? trim($name) : $name,
            'timezone' => is_string($timezone) ? trim($timezone) : $timezone,
        ]);
    }
}
