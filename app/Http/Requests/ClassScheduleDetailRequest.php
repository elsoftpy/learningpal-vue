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
                'nullable', 
                'date',
            ],
            'rescheduled_start_time' => [
                'nullable', 
                'date',
            ],
            'rescheduled_end_time' => [
                'nullable', 
                'date', 
                'after:rescheduled_start_time',
            ],
            'status' => Rule::when(
                $canEditStatus,
                ['nullable', 'string', Rule::in(ClassScheduleStatusEnum::values())],
                ['prohibited']
            ),
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('session_date')) {
            $sessionDateString = $this->session_date;
            $sessionDate = DateTimeService::dateFromLocalizedString($this->session_date);
        }

        if ($this->has('start_time')) {
            $startTimeString = $sessionDateString.' '.$this->start_time.':00';
            $startTime = DateTimeService::dateTimeFromLocalizedString($startTimeString);
        }

        if ($this->has('end_time')) {
            $endTimeString = $sessionDateString.' '.$this->end_time.':00';
            $endTime = DateTimeService::dateTimeFromLocalizedString($endTimeString);
        }

        if ($this->has('rescheduled_date')) {
            $rescheduledDateString = $this->rescheduled_date;
            $rescheduledDate = DateTimeService::dateFromLocalizedString($this->rescheduled_date);
        }

        if ($this->has('rescheduled_start_time')) {
            $rescheduledStartTimeString = $rescheduledDateString.' '.$this->rescheduled_start_time.':00';
            $rescheduledStartTime = DateTimeService::dateTimeFromLocalizedString($rescheduledStartTimeString);
        }

        if ($this->has('rescheduled_end_time')) {
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
