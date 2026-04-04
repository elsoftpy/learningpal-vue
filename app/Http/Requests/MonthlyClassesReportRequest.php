<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Services\Authorization\CourseVisibilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MonthlyClassesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'integer',
                'exists:courses,id',
            ],
            'student_id' => [
                'nullable',
                'integer',
                'exists:students,id',
            ],
            'month' => [
                'required',
                'date_format:Y-m',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $courseId = (int) $this->input('course_id');
            $studentId = $this->input('student_id');

            if (! $courseId || ! $studentId) {
                if (! $courseId) {
                    return;
                }
            }

            if (!(new CourseVisibilityService())->canAccessCourseId($this->user(), $courseId)) {
                $validator->errors()->add(
                    'course_id',
                    __('The selected course is not available.')
                );
                return;
            }

            if (! $studentId) {
                return;
            }

            $belongsToCourse = Course::query()
                ->whereKey($courseId)
                ->whereHas('students', function ($query) use ($studentId) {
                    $query->where('students.id', (int) $studentId);
                })
                ->exists();

            if (! $belongsToCourse) {
                $validator->errors()->add(
                    'student_id',
                    __('The selected student does not belong to the selected course.')
                );
            }
        });
    }
}
