<?php

namespace App\Http\Requests;

use App\Enums\StudyProgramActivityTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StudyProgramWeekActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $activityId = $this->activity?->id;
        $studyProgramWeekId = $this->activity?->study_program_week_id ?? $this->week?->id;

        return [
            'level_content_id' => ['nullable', 'integer', 'exists:level_contents,id'],
            'free_content' => ['nullable', 'string'],
            'activity_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(StudyProgramActivityTypeEnum::values())],
            'links' => ['nullable', 'string'],
            'study_material' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpeg,jpg,png,webp',
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('study_program_week_activities', 'sort_order')
                    ->where(fn ($query) => $query->where('study_program_week_id', $studyProgramWeekId))
                    ->ignore($activityId),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasLevelContent = $this->filled('level_content_id');
            $freeContent = $this->input('free_content');
            $hasFreeContent = is_string($freeContent) && trim($freeContent) !== '';

            if ($hasLevelContent && $hasFreeContent) {
                $validator->errors()->add('free_content', __('Free content must be empty when content topic is selected.'));
            }

            if (!$hasLevelContent && !$hasFreeContent) {
                $validator->errors()->add('free_content', __('Free content is required when no content topic is selected.'));
            }

            $links = $this->input('links');
            $hasLinks = is_string($links) && trim($links) !== '';

            if ($this->input('type') === StudyProgramActivityTypeEnum::VIDEO->value && !$hasLinks) {
                $validator->errors()->add('links', __('A video activity requires at least one link.'));
            }
        });
    }

    public function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('free_content')) {
            $freeContent = $this->input('free_content');

            if (is_string($freeContent)) {
                $freeContent = trim($freeContent);
            }

            $payload['free_content'] = $freeContent !== '' ? $freeContent : null;
        }

        if ($this->has('links')) {
            $links = $this->input('links');

            if (is_string($links)) {
                $links = trim($links);
            }

            $payload['links'] = $links !== '' ? $links : null;
        }

        if (!empty($payload)) {
            $this->merge($payload);
        }
    }
}
