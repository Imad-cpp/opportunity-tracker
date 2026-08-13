<?php

namespace App\Http\Requests;

use App\Opportunities\DeadlineInput;
use App\Rules\HttpUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            ...DeadlineInput::rules($this),
            'owner_id' => ['prohibited'],
            'status' => ['prohibited'],
            'archived_at' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        foreach (['type', 'priority', 'title', 'organization', 'source_url', 'location', 'notes', 'next_action', 'next_action_at', 'deadline_at', 'deadline_precision', 'deadline_timezone'] as $key) {
            if (! array_key_exists($key, $input) || ! is_string($input[$key])) {
                continue;
            }
            $value = trim($input[$key]);
            $input[$key] = in_array($key, ['source_url', 'location', 'notes', 'next_action', 'next_action_at'], true) && $value === '' ? null : $value;
        }
        $this->merge($input);
    }

    public function withValidator(Validator $validator): void
    {
        DeadlineInput::validate($this, $validator);
    }
}
