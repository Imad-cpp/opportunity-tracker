<?php

namespace App\Opportunities;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class DeadlineInput
{
    public static function rules(FormRequest $request): array
    {
        return [
            'deadline_at' => ['sometimes', 'nullable', 'string', 'max:32', Rule::when($request->input('deadline_precision') === 'DATE', ['date_format:Y-m-d']), Rule::when($request->input('deadline_precision') === 'DATETIME', ['date_format:Y-m-d\\TH:i'])],
            'deadline_precision' => ['sometimes', 'nullable', 'string', Rule::in(['DATE', 'DATETIME']), Rule::requiredIf(fn (): bool => $request->filled('deadline_at'))],
            'deadline_timezone' => ['sometimes', 'nullable', 'string', 'max:64', 'timezone', Rule::requiredIf(fn (): bool => $request->input('deadline_precision') === 'DATETIME')],
        ];
    }

    public static function validate(FormRequest $request, Validator $validator): void
    {
        $validator->after(function (Validator $validator) use ($request): void {
            $hasAt = $request->exists('deadline_at');
            $hasPrecision = $request->exists('deadline_precision');
            $hasTimezone = $request->exists('deadline_timezone');

            if (! $hasAt && ($hasPrecision || $hasTimezone)) {
                $validator->errors()->add('deadline_at', 'deadline_at is required when deadline metadata is supplied.');
                return;
            }

            if (! $hasAt) {
                return;
            }

            if ($request->input('deadline_at') === null) {
                if ($request->input('deadline_precision') !== null || $request->input('deadline_timezone') !== null) {
                    $validator->errors()->add('deadline_at', 'Deadline metadata must be null or omitted when clearing a deadline.');
                }
                return;
            }

            if ($request->input('deadline_precision') === 'DATE' && $request->input('deadline_timezone') !== null) {
                $validator->errors()->add('deadline_timezone', 'Date-only deadlines use the account timezone and must not supply deadline_timezone.');
            }
        });
    }

    public static function attributes(array $validated, User $user): array
    {
        if (! array_key_exists('deadline_at', $validated)) {
            return [];
        }

        if ($validated['deadline_at'] === null) {
            return ['deadline_at' => null, 'deadline_precision' => null, 'deadline_timezone' => null];
        }

        if ($validated['deadline_precision'] === 'DATE') {
            $timezone = $user->timezone;
            $deadline = CarbonImmutable::createFromFormat('!Y-m-d', $validated['deadline_at'], $timezone)->endOfDay()->utc();
            return ['deadline_at' => $deadline, 'deadline_precision' => 'DATE', 'deadline_timezone' => $timezone];
        }

        $timezone = $validated['deadline_timezone'];
        $deadline = CarbonImmutable::createFromFormat('!Y-m-d\\TH:i', $validated['deadline_at'], $timezone)->utc();
        return ['deadline_at' => $deadline, 'deadline_precision' => 'DATETIME', 'deadline_timezone' => $timezone];
    }

    public static function dateBoundary(string $date, string $timezone, bool $endOfDay): CarbonImmutable
    {
        $boundary = CarbonImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        return ($endOfDay ? $boundary->endOfDay() : $boundary->startOfDay())->utc();
    }

    public static function withoutRawFields(array $validated): array
    {
        unset($validated['deadline_at'], $validated['deadline_precision'], $validated['deadline_timezone']);
        return $validated;
    }
}
