<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'SAVED',
                    'PREPARING',
                    'APPLIED',
                    'INTERVIEWING',
                    'OFFERED',
                    'ACCEPTED',
                    'REJECTED',
                    'WITHDRAWN',
                    'EXPIRED',
                ]),
            ],
        ];
    }
}
