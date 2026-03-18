<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatusEnum;
use App\Services\Utilities\DateTimeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassRecordRequest extends FormRequest
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
            'teacher_id' => [
                'required', 
                'exists:teachers,id',
            ],
            'class_schedule_detail_id' => [
                'required', 
                'exists:class_schedule_details,id',
            ],
            'date' => [
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
            'duration_minutes' => [
                'required', 
                'integer', 
                'min:1',
            ],
            'attendance' => [
                'required',
                Rule::in(AttendanceStatusEnum::values()),
            ],
            'comments' => [
                'nullable', 
                'string', 
                'max:255',
            ],
            'details' => [
                'nullable',
                'array',
            ],
            'details.*.id' => [
                'nullable',
                'integer',
                'exists:class_record_details,id',
            ],
            'details.*.content_id' => [
                'nullable',
                'integer',
                'exists:level_contents,id',
            ],
            'details.*.free_content' => [
                'nullable',
                'string',
                'max:255',
            ],
            'details.*.activity' => [
                'required',
                'string',
                'max:255',
            ],
            'details.*.links' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'detail_files' => [
                'nullable',
                'array',
            ],
            'detail_files.*' => [
                'nullable',
                'file',
                'max:10240',
            ],
            'student_production_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,txt,jpeg,jpg,png,webp',
            ],
            'student_production_audio' => [
                'nullable',
                'file',
                'max:10240',
                'mimetypes:audio/mpeg,audio/mp3,audio/webm,audio/webm;codecs=opus,audio/ogg,video/webm',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'student_production_audio.mimetypes' => 'El campo student production audio debe ser un archivo de tipo: audio/mpeg, audio/mp3, audio/webm, audio/ogg.',
        ];
    }

    public function prepareForValidation()
    {
        $dateString = $this->date;

        if ($this->has('details') && is_array($this->details)) {
            $details = collect($this->details)
                ->map(function ($detail) {
                    if (!is_array($detail)) {
                        return $detail;
                    }

                    $links = $detail['links'] ?? null;
                    if (is_string($links) && $links !== '') {
                        $detail['links'] = collect(preg_split('/[\s,|]+/', $links) ?: [])
                            ->filter(fn ($value) => filled($value))
                            ->implode('|');
                    }

                    return $detail;
                })
                ->values()
                ->all();

            $this->merge(['details' => $details]);
        }

        if ($this->has('attendance') && $this->attendance !== null) {
            $this->merge([
                'attendance' => (string) $this->attendance,
            ]);
        }

        if ($this->has('date')) {
            $date = DateTimeService::dateFromLocalizedString($this->date);

            $this->merge([
                'date' => $date ?? null,
            ]);
        }

        if ($this->has('start_time')) {
            $startTimeString = $dateString.' '.$this->start_time.':00';
            $startTime = DateTimeService::dateTimeFromLocalizedString($startTimeString);

            $this->merge([
                'start_time' => $startTime ?? null,
            ]);
        }

        if ($this->has('end_time')) {
            $endTimeString = $dateString.' '.$this->end_time.':00';
            $endTime = DateTimeService::dateTimeFromLocalizedString($endTimeString);

            $this->merge([
                'end_time' => $endTime ?? null,
            ]);
        }
    }
}
