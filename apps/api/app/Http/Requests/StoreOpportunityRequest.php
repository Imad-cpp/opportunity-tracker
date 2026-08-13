<?php

namespace App\Http\Requests;

use App\Rules\HttpUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['JOB', 'INTERNSHIP', 'SCHOLARSHIP', 'PROGRAM', 'OTHER'])],
            'priority' => ['required', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'title' => ['required', 'string', 'max:200'],
            'organization' => ['required', 'string', 'max:200'],
            'source_url' => ['nullable', 'string', 'max:2048', new HttpUrl],
            'location' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'next_action' => ['nullable', 'string', 'max:500'],
            'next_action_at' => ['nullable', 'date'],
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
        $this->merge($this->normalizedInput());
    }

    private function normalizedInput(): array
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

        return $input;
    }
}
