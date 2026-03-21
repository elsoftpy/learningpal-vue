<?php

namespace App\Http\Requests;

use App\Enums\StudyProgramActivityTypeEnum;
use App\Enums\StudyProgramStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StudyProgramRequest extends FormRequest
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
        $studyProgramId = $this->studyProgram?->id;
        $weeksRule = $studyProgramId ? ['sometimes', 'array', 'min:1'] : ['required', 'array', 'min:1'];

        return [
            'language_level_id' => [
                'required',
                'integer',
                'exists:language_levels,id',
                Rule::unique('study_programs', 'language_level_id')->ignore($studyProgramId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(StudyProgramStatusEnum::values())],
            'weeks' => $weeksRule,
            'weeks.*.week_number' => ['required', 'integer', 'min:1', 'distinct'],
            'weeks.*.title' => ['required', 'string', 'max:255'],
            'weeks.*.status' => ['required', 'string', Rule::in(StudyProgramStatusEnum::values())],
            'weeks.*.activities' => ['required', 'array', 'min:1'],
            'weeks.*.activities.*.level_content_id' => ['nullable', 'integer', 'exists:level_contents,id'],
            'weeks.*.activities.*.free_content' => ['nullable', 'string'],
            'weeks.*.activities.*.activity_name' => ['required', 'string', 'max:255'],
            'weeks.*.activities.*.type' => ['required', 'string', Rule::in(StudyProgramActivityTypeEnum::values())],
            'weeks.*.activities.*.links' => ['nullable', 'string'],
            'weeks.*.activities.*.study_material' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpeg,jpg,png,webp',
            ],
            'weeks.*.activities.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $weeks = $this->input('weeks', []);

            if (!is_array($weeks)) {
                return;
            }

            foreach ($weeks as $weekIndex => $week) {
                $activities = is_array($week['activities'] ?? null) ? $week['activities'] : [];

                foreach ($activities as $activityIndex => $activity) {
                    if (!is_array($activity)) {
                        continue;
                    }

                    $hasLevelContent = isset($activity['level_content_id']) && $activity['level_content_id'] !== null && $activity['level_content_id'] !== '';
                    $freeContent = $activity['free_content'] ?? null;
                    $hasFreeContent = is_string($freeContent) && trim($freeContent) !== '';
                    $field = "weeks.$weekIndex.activities.$activityIndex.free_content";

                    if ($hasLevelContent && $hasFreeContent) {
                        $validator->errors()->add($field, __('Free content must be empty when content topic is selected.'));
                    }

                    if (!$hasLevelContent && !$hasFreeContent) {
                        $validator->errors()->add($field, __('Free content is required when no content topic is selected.'));
                    }

                    $links = $activity['links'] ?? null;
                    $hasLinks = is_string($links) && trim($links) !== '';
                    $isVideoActivity = ($activity['type'] ?? null) === StudyProgramActivityTypeEnum::VIDEO->value;

                    if ($isVideoActivity && !$hasLinks) {
                        $validator->errors()->add("weeks.$weekIndex.activities.$activityIndex.links", __('A video activity requires at least one link.'));
                    }
                }
            }
        });
    }

    public function prepareForValidation(): void
    {
        if (!is_array($this->weeks)) {
            return;
        }

        $weeks = collect($this->weeks)
            ->map(function ($week) {
                if (!is_array($week) || !isset($week['activities']) || !is_array($week['activities'])) {
                    return $week;
                }

                $week['activities'] = collect($week['activities'])
                    ->map(function ($activity) {
                        if (!is_array($activity)) {
                            return $activity;
                        }

                        $freeContent = $activity['free_content'] ?? null;

                        if (is_string($freeContent)) {
                            $freeContent = trim($freeContent);
                            $activity['free_content'] = $freeContent !== '' ? $freeContent : null;
                        }

                        $links = $activity['links'] ?? null;

                        if (is_string($links)) {
                            $links = trim($links);
                            $activity['links'] = $links !== '' ? $links : null;
                        }

                        return $activity;
                    })
                    ->values()
                    ->all();

                return $week;
            })
            ->values()
            ->all();

        $this->merge([
            'weeks' => $weeks,
        ]);
    }
}
