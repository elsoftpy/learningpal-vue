<?php

namespace App\Http\Requests;

use App\Enums\ClassScheduleStatusEnum;
use App\Services\Utilities\DateTimeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassScheduleDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $canEditStatus = (bool) $this->user()?->can('change schedule detail status');
        $hasAnyRescheduleValue = collect([
            'rescheduled_date',
            'rescheduled_start_time',
            'rescheduled_end_time',
        ])->contains(fn (string $field) => $this->filled($field));
        $requiresReschedule = $this->input('status') === ClassScheduleStatusEnum::REPROGRAMED->value
            || $hasAnyRescheduleValue;

        return [
            'session_date' => [
                'required', 
                'date',
            ],
            'start_time' => [
                'required', 
                'date',
            ],
            'end_time' => [
                'required', 
                'date', 
                'after:start_time',
            ],
            'topic' => [
                'nullable', 
                'string', 
                'max:500',
            ],
            'activity' => [
                'nullable', 
                'string', 
                'max:500',
            ],
            'rescheduled_date' => [
                Rule::when($requiresReschedule, ['required', 'date'], ['nullable', 'date']),
            ],
            'rescheduled_start_time' => [
                Rule::when($requiresReschedule, ['required', 'date'], ['nullable', 'date']),
            ],
            'rescheduled_end_time' => [
                Rule::when($requiresReschedule, ['required', 'date', 'after:rescheduled_start_time'], ['nullable', 'date', 'after:rescheduled_start_time']),
            ],
            'status' => Rule::when(
                $canEditStatus,
                ['nullable', 'string', Rule::in(ClassScheduleStatusEnum::values())],
                ['prohibited']
            ),
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasAnyRescheduleValue = collect([
                'rescheduled_date',
                'rescheduled_start_time',
                'rescheduled_end_time',
            ])->contains(fn (string $field) => $this->filled($field));

            if ($hasAnyRescheduleValue && $this->input('status') === ClassScheduleStatusEnum::CANCELED->value) {
                $validator->errors()->add(
                    'rescheduled_date',
                    __('Cannot reschedule a cancelled session. Please change the status first.')
                );
            }
        });
    }

    public function prepareForValidation()
    {
        $sessionDateString = null;
        $rescheduledDateString = null;

        if ($this->filled('session_date')) {
            $sessionDateString = $this->session_date;
            $sessionDate = DateTimeService::dateFromLocalizedString($this->session_date);
        }

        if ($this->filled('start_time') && $sessionDateString) {
            $startTimeString = $sessionDateString.' '.$this->start_time.':00';
            $startTime = DateTimeService::dateTimeFromLocalizedString($startTimeString);
        }

        if ($this->filled('end_time') && $sessionDateString) {
            $endTimeString = $sessionDateString.' '.$this->end_time.':00';
            $endTime = DateTimeService::dateTimeFromLocalizedString($endTimeString);
        }

        if ($this->filled('rescheduled_date')) {
            $rescheduledDateString = $this->rescheduled_date;
            $rescheduledDate = DateTimeService::dateFromLocalizedString($this->rescheduled_date);
        }

        if ($this->filled('rescheduled_start_time') && $rescheduledDateString) {
            $rescheduledStartTimeString = $rescheduledDateString.' '.$this->rescheduled_start_time.':00';
            $rescheduledStartTime = DateTimeService::dateTimeFromLocalizedString($rescheduledStartTimeString);
        }

        if ($this->filled('rescheduled_end_time') && $rescheduledDateString) {
            $rescheduledEndTimeString = $rescheduledDateString.' '.$this->rescheduled_end_time.':00';
            $rescheduledEndTime = DateTimeService::dateTimeFromLocalizedString($rescheduledEndTimeString);
        }

        $this->merge([
            'session_date' => $sessionDate ?? null,
            'start_time' => $startTime ?? null,
            'end_time' => $endTime ?? null,
            'rescheduled_date' => $rescheduledDate ?? null,
            'rescheduled_start_time' => $rescheduledStartTime ?? null,
            'rescheduled_end_time' => $rescheduledEndTime ?? null,
        ]);

    }
}
