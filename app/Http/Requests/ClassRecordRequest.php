<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatusEnum;
use App\Models\ClassScheduleDetail;
use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'student_attendances' => [
                'required',
                'array',
                'min:1',
            ],
            'student_attendances.*.student_id' => [
                'required',
                'integer',
                'exists:students,id',
                'distinct',
            ],
            'student_attendances.*.attendance' => [
                'required',
                Rule::in(AttendanceStatusEnum::values()),
            ],
            'comments' => [
                'required', 
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classScheduleDetailId = $this->input('class_schedule_detail_id');
            $studentAttendances = collect($this->input('student_attendances', []));

            if (!$classScheduleDetailId || $studentAttendances->isEmpty()) {
                return;
            }

            $classScheduleDetail = ClassScheduleDetail::query()
                ->with('classSchedule.course.students')
                ->find($classScheduleDetailId);

            $courseId = $classScheduleDetail?->classSchedule?->course_id;

            if (!$courseId) {
                return;
            }

            $validStudentIds = $classScheduleDetail
                ?->classSchedule
                ?->course
                ?->students
                ?->pluck('id')
                ?->map(fn ($id) => (int) $id)
                ?->all() ?? [];

            foreach ($studentAttendances as $index => $item) {
                $studentId = (int) ($item['student_id'] ?? 0);

                if (!in_array($studentId, $validStudentIds, true)) {
                    $validator->errors()->add(
                        "student_attendances.{$index}.student_id",
                        __('The selected student does not belong to this course.')
                    );
                }
            }

            if (! $studentAttendances->every(fn ($item) => (string) ($item['attendance'] ?? '') === AttendanceStatusEnum::ABSENT->value)) {
                return;
            }

            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $durationMinutes = $this->input('duration_minutes');

            if (!$startTime || !$endTime || $durationMinutes === null) {
                return;
            }

            try {
                $sessionDuration = Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime));
            } catch (\Throwable) {
                return;
            }

            $graceMinutes = (int) config('academics.class_records.absent_duration_grace_minutes', 10);
            $maxAbsentDuration = max(1, $sessionDuration - $graceMinutes);

            if ((int) $durationMinutes > $maxAbsentDuration) {
                $validator->errors()->add(
                    'duration_minutes',
                    __('When attendance is absent, duration must be less than or equal to :minutes minutes.', [
                        'minutes' => $maxAbsentDuration,
                    ])
                );
            }
        });
    }

    public function prepareForValidation()
    {
        $dateString = $this->date;
        $startTime = null;
        $endTime = null;

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

        if ($this->has('student_attendances') && is_array($this->student_attendances)) {
            $normalizedAttendances = collect($this->student_attendances)
                ->map(function ($item) {
                    if (!is_array($item)) {
                        return $item;
                    }

                    if (array_key_exists('attendance', $item) && $item['attendance'] !== null) {
                        $item['attendance'] = (string) $item['attendance'];
                    }

                    if (array_key_exists('student_id', $item) && $item['student_id'] !== null) {
                        $item['student_id'] = (int) $item['student_id'];
                    }

                    return $item;
                })
                ->values()
                ->all();

            $this->merge([
                'student_attendances' => $normalizedAttendances,
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

        if ($startTime && $endTime) {
            $sessionDuration = Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime));
            $studentAttendances = collect($this->input('student_attendances', []));

            $allAbsent = $studentAttendances->isNotEmpty()
                && $studentAttendances->every(fn ($item) => (string) ($item['attendance'] ?? '') === AttendanceStatusEnum::ABSENT->value);

            $durationMinutes = $sessionDuration;

            if ($allAbsent) {
                $graceMinutes = (int) config('academics.class_records.absent_duration_grace_minutes', 10);
                $durationMinutes = max(1, $sessionDuration - $graceMinutes);
            }

            $this->merge([
                'duration_minutes' => $durationMinutes,
            ]);
        }
    }
}
