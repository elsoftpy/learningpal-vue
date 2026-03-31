<?php

namespace App\Http\Requests;

use App\Services\Utilities\DateTimeService;
use Illuminate\Foundation\Http\FormRequest;

class ClassScheduleRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'course_id' => [
                'required', 
                'exists:courses,id',
            ],
            'schedule_month' => [
                'required', 
                'date',
            ],
            'details' => [
                'required', 
                'array', 
                'min:1',
            ],
            'details.*.id' => [
                'nullable',
                'integer',
                'exists:class_schedule_details,id',
            ],
            'details.*.session_date' => [
                'required', 
                'date',
            ],
            'details.*.start_time' => [
                'required', 
                'date',
            ],
            'details.*.end_time' => [
                'required', 
                'date', 
                'after:details.*.start_time',
            ],
            'details.*.topic' => [
                'nullable', 
                'string', 
                'max:500',
            ],
            'details.*.activity' => [
                'nullable', 
                'string', 
                'max:500',
            ],
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('schedule_month')) {
            $scheduleMonth = DateTimeService::monthYearFromLocalizedString($this->schedule_month);
        }

        $this->merge([
            'schedule_month' => $scheduleMonth ?? null,
        ]);

        // Prepare details dates
        if ($this->has('details') && is_array($this->details)) {
            $preparedDetails = [];
            foreach ($this->details as $detail) {
                $sessionDateString = null;
                if (isset($detail['session_date'])) {
                    $sessionDateString = $detail['session_date'];
                    $detail['session_date'] = DateTimeService::dateFromLocalizedString($sessionDateString);
                }

                if (isset($detail['start_time'])) {
                    $startTime = $sessionDateString . ' ' . $detail['start_time'].':00';
                    $detail['start_time'] = DateTimeService::dateTimeFromLocalizedString($startTime);
                }

                if (isset($detail['end_time'])) {
                    $endTime = $sessionDateString . ' ' . $detail['end_time'].':00';
                    $detail['end_time'] = DateTimeService::dateTimeFromLocalizedString($endTime);
                }

                $preparedDetails[] = $detail;
            }
            $this->merge([
                'details' => $preparedDetails,
            ]);
        }
    }
}
