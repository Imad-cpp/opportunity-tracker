<?php

namespace App\Http\Requests;

use App\Rules\HttpUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'string', Rule::in(['JOB', 'INTERNSHIP', 'SCHOLARSHIP', 'PROGRAM', 'OTHER'])],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'organization' => ['sometimes', 'required', 'string', 'max:200'],
            'source_url' => ['sometimes', 'nullable', 'string', 'max:2048', new HttpUrl],
            'location' => ['sometimes', 'nullable', 'string', 'max:200'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'next_action' => ['sometimes', 'nullable', 'string', 'max:500'],
            'next_action_at' => ['sometimes', 'nullable', 'date'],
            'owner_id' => ['prohibited'],
            'status' => ['prohibited'],
            'deadline_at' => ['prohibited'],
            'deadline_precision' => ['prohibited'],
            'deadline_timezone' => ['prohibited'],
            'archived_at' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        foreach (['type', 'priority', 'title', 'organization', 'source_url', 'location', 'notes', 'next_action'] as $key) {
            if (! array_key_exists($key, $input) || ! is_string($input[$key])) {
                continue;
            }

            $value = trim($input[$key]);
            $input[$key] = in_array($key, ['source_url', 'location', 'notes', 'next_action'], true) && $value === '' ? null : $value;
        }

        if (array_key_exists('next_action_at', $input) && is_string($input['next_action_at'])) {
            $value = trim($input['next_action_at']);
            $input['next_action_at'] = $value === '' ? null : $value;
        }

        $this->merge($input);
    }
}
