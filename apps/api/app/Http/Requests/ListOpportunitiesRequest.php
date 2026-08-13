<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListOpportunitiesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'string', Rule::in(['SAVED', 'PREPARING', 'APPLIED', 'INTERVIEWING', 'OFFERED', 'ACCEPTED', 'REJECTED', 'WITHDRAWN', 'EXPIRED'])],
            'type' => ['sometimes', 'string', Rule::in(['JOB', 'INTERNSHIP', 'SCHOLARSHIP', 'PROGRAM', 'OTHER'])],
            'priority' => ['sometimes', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'archived' => ['sometimes', 'boolean'],
            'deadline_from' => ['sometimes', 'date_format:Y-m-d'],
            'deadline_to' => ['sometimes', 'date_format:Y-m-d'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        if (isset($input['q']) && is_string($input['q'])) { $input['q'] = trim($input['q']); }
        if (($input['archived'] ?? null) === 'true') { $input['archived'] = true; }
        elseif (($input['archived'] ?? null) === 'false') { $input['archived'] = false; }
        $this->merge($input);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $from = $this->input('deadline_from');
            $to = $this->input('deadline_to');
            if (is_string($from) && is_string($to) && $to < $from) {
                $validator->errors()->add('deadline_to', 'deadline_to must be on or after deadline_from.');
            }
        });
    }
}
