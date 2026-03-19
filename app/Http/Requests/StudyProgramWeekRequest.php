<?php

namespace App\Http\Requests;

use App\Enums\StudyProgramStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyProgramWeekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $weekId = $this->week?->id;
        $studyProgramId = $this->week?->study_program_id ?? $this->studyProgram?->id;

        return [
            'week_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('study_program_weeks', 'week_number')
                    ->where(fn ($query) => $query->where('study_program_id', $studyProgramId))
                    ->ignore($weekId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(StudyProgramStatusEnum::values())],
        ];
    }
}
