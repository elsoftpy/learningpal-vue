<?php

namespace App\Services\Academics\Settings;

use App\Models\DistanceActivityDetailStudent;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use App\Models\StudyProgramWeekActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StudyProgramService
{
    public function studyProgramData(StudyProgram $studyProgram): array
    {
        $studyProgram->loadMissing([
            'languageLevel.language',
            'weeks.activities.levelContent',
            'weeks.activities.media',
        ]);

        return [
            'id' => $studyProgram->id,
            'language_level_id' => $studyProgram->language_level_id,
            'title' => $studyProgram->title,
            'status' => $studyProgram->status->value,
            'display_status' => $studyProgram->status->label($studyProgram->status->value),
            'language_level' => [
                'id' => $studyProgram->languageLevel->id,
                'description' => $studyProgram->languageLevel->description,
                'level' => $studyProgram->languageLevel->level,
                'language' => [
                    'id' => $studyProgram->languageLevel->language->id,
                    'name' => $studyProgram->languageLevel->language->name,
                ],
            ],
            'weeks' => $studyProgram->weeks
                ->sortBy('week_number')
                ->values()
                ->map(function (StudyProgramWeek $week) {
                    return [
                        'id' => $week->id,
                        'week_number' => $week->week_number,
                        'title' => $week->title,
                        'status' => $week->status->value,
                        'display_status' => $week->status->label($week->status->value),
                        'activities' => $week->activities
                            ->sortBy('sort_order')
                            ->values()
                            ->map(function ($activity) {
                                return [
                                    'id' => $activity->id,
                                    'level_content_id' => $activity->level_content_id,
                                    'level_content' => $activity->levelContent?->content,
                                    'free_content' => $activity->free_content,
                                    'activity_name' => $activity->activity_name,
                                    'type' => $activity->type->value,
                                    'display_type' => $activity->type->label($activity->type->value),
                                    'links' => $activity->links,
                                    'sort_order' => $activity->sort_order,
                                    'study_material_url' => $activity->getFirstMedia('study-materials')?->getUrl(),
                                    'study_material_name' => $activity->getFirstMedia('study-materials')?->file_name,
                                ];
                            })
                            ->toArray(),
                    ];
                })
                ->toArray(),
        ];
    }

    public function createStudyProgram(array $data): StudyProgram
    {
        return DB::transaction(function () use ($data) {
            $studyProgram = StudyProgram::query()->create([
                'language_level_id' => $data['language_level_id'],
                'title' => $data['title'],
                'status' => $data['status'],
            ]);

            $this->syncWeeks($studyProgram, $data['weeks']);

            (new StudyProgramReplicationService())->propagateStudyProgramCreated($studyProgram);

            return $studyProgram->fresh(['languageLevel.language', 'weeks.activities.levelContent']);
        });
    }

    public function updateStudyProgram(StudyProgram $studyProgram, array $data): StudyProgram
    {
        return DB::transaction(function () use ($studyProgram, $data) {
            $studyProgram->update([
                'language_level_id' => $data['language_level_id'],
                'title' => $data['title'],
                'status' => $data['status'],
            ]);

            return $studyProgram->fresh(['languageLevel.language', 'weeks.activities.levelContent']);
        });
    }

    public function deleteStudyProgram(StudyProgram $studyProgram): void
    {
        DB::transaction(function () use ($studyProgram) {
            $this->deleteWeeks($studyProgram);
            $studyProgram->delete();
        });
    }

    protected function syncWeeks(StudyProgram $studyProgram, array $weeks): void
    {
        foreach ($weeks as $weekData) {
            $week = $studyProgram->weeks()->create([
                'week_number' => $weekData['week_number'],
                'title' => $weekData['title'],
                'status' => $weekData['status'],
            ]);

            foreach ($weekData['activities'] as $index => $activityData) {
                $activity = $week->activities()->create([
                    'level_content_id' => $activityData['level_content_id'] ?? null,
                    'free_content' => $activityData['free_content'] ?? null,
                    'activity_name' => $activityData['activity_name'],
                    'type' => $activityData['type'],
                    'links' => $activityData['links'] ?? null,
                    'sort_order' => $activityData['sort_order'] ?? ($index + 1),
                ]);

                $this->syncStudyProgramWeekActivityStudyMaterial($activity, $activityData['study_material'] ?? null);
            }
        }
    }

    protected function deleteWeeks(StudyProgram $studyProgram): void
    {
        $studyProgram->loadMissing('weeks.activities');

        foreach ($studyProgram->weeks as $week) {
            $week->activities()->delete();
        }

        $studyProgram->weeks()->delete();
    }

    public function studyProgramWeekData(StudyProgramWeek $week): array
    {
        $week->loadMissing('studyProgram.languageLevel.language', 'activities');

        return [
            'id' => $week->id,
            'week_number' => $week->week_number,
            'title' => $week->title,
            'status' => $week->status->value,
            'display_status' => $week->status->label($week->status->value),
            'study_program' => [
                'id' => $week->studyProgram->id,
                'title' => $week->studyProgram->title,
                'language_level_id' => $week->studyProgram->language_level_id,
                'language_level' => [
                    'id' => $week->studyProgram->languageLevel->id,
                    'description' => $week->studyProgram->languageLevel->description,
                    'level' => $week->studyProgram->languageLevel->level,
                    'language' => [
                        'id' => $week->studyProgram->languageLevel->language->id,
                        'name' => $week->studyProgram->languageLevel->language->name,
                    ],
                ],
            ],
            'activities' => $week->activities
                ->sortBy('sort_order')
                ->values()
                ->map(fn (StudyProgramWeekActivity $activity) => [
                    'id' => $activity->id,
                    'sort_order' => $activity->sort_order,
                ])
                ->toArray(),
        ];
    }

    public function createStudyProgramWeek(StudyProgram $studyProgram, array $data): StudyProgramWeek
    {
        $week = $studyProgram->weeks()->create([
            'week_number' => $data['week_number'],
            'title' => $data['title'],
            'status' => $data['status'],
        ]);

        (new StudyProgramReplicationService())->propagateWeekCreated($week);

        return $week->fresh(['studyProgram.languageLevel.language']);
    }

    public function updateStudyProgramWeek(StudyProgramWeek $week, array $data): StudyProgramWeek
    {
        $week->update([
            'week_number' => $data['week_number'],
            'title' => $data['title'],
            'status' => $data['status'],
        ]);

        (new StudyProgramReplicationService())->propagateWeekUpdated($week);

        return $week->fresh(['studyProgram.languageLevel.language']);
    }

    public function deleteStudyProgramWeek(StudyProgramWeek $week): void
    {
        DB::transaction(function () use ($week) {
            $week->activities()->delete();
            $week->delete();
        });
    }

    public function weekHasStudentInteractions(StudyProgramWeek $week): bool
    {
        return DistanceActivityDetailStudent::query()
            ->where('completed', true)
            ->whereHas('distanceActivityDetail.distanceActivity', function ($query) use ($week) {
                $query->where('study_program_week_id', $week->id);
            })
            ->exists();
    }

    public function studyProgramWeekActivityData(StudyProgramWeekActivity $activity): array
    {
        $activity->loadMissing('studyProgramWeek.studyProgram.languageLevel.language', 'levelContent', 'media');
        $studyProgram = $activity->studyProgramWeek->studyProgram;
        $languageLevel = $studyProgram->languageLevel;
        $studyMaterial = $activity->getFirstMedia('study-materials');

        return [
            'id' => $activity->id,
            'level_content_id' => $activity->level_content_id,
            'free_content' => $activity->free_content,
            'activity_name' => $activity->activity_name,
            'type' => $activity->type->value,
            'display_type' => $activity->type->label($activity->type->value),
            'links' => $activity->links,
            'sort_order' => $activity->sort_order,
            'study_material_url' => $studyMaterial?->getUrl(),
            'study_material_name' => $studyMaterial?->file_name,
            'study_program_week' => [
                'id' => $activity->studyProgramWeek->id,
                'week_number' => $activity->studyProgramWeek->week_number,
                'title' => $activity->studyProgramWeek->title,
                'study_program' => [
                    'id' => $studyProgram->id,
                    'title' => $studyProgram->title,
                    'language_level_id' => $studyProgram->language_level_id,
                    'language_level' => [
                        'id' => $languageLevel->id,
                        'description' => $languageLevel->description,
                        'level' => $languageLevel->level,
                        'language' => [
                            'id' => $languageLevel->language->id,
                            'name' => $languageLevel->language->name,
                        ],
                    ],
                ],
            ],
            'level_contents' => $languageLevel->levelContents()
                ->orderBy('content')
                ->get()
                ->map(fn ($content) => [
                    'id' => $content->id,
                    'content' => $content->content,
                ])
                ->toArray(),
        ];
    }

    public function studyProgramWeekActivityCreateData(StudyProgramWeek $week): array
    {
        $week->loadMissing('studyProgram.languageLevel.language', 'studyProgram.languageLevel.levelContents', 'activities');
        $studyProgram = $week->studyProgram;
        $languageLevel = $studyProgram->languageLevel;

        return [
            'id' => $week->id,
            'week_number' => $week->week_number,
            'title' => $week->title,
            'study_program' => [
                'id' => $studyProgram->id,
                'title' => $studyProgram->title,
                'language_level_id' => $studyProgram->language_level_id,
            ],
            'activities' => $week->activities
                ->sortBy('sort_order')
                ->values()
                ->map(fn (StudyProgramWeekActivity $activity) => [
                    'id' => $activity->id,
                    'sort_order' => $activity->sort_order,
                ])
                ->toArray(),
            'level_contents' => $languageLevel->levelContents
                ->sortBy('content')
                ->values()
                ->map(fn ($content) => [
                    'id' => $content->id,
                    'content' => $content->content,
                ])
                ->toArray(),
        ];
    }

    public function createStudyProgramWeekActivity(
        StudyProgramWeek $week,
        array $data,
        ?UploadedFile $studyMaterialFile = null
    ): StudyProgramWeekActivity
    {
        $activity = $week->activities()->create([
            'level_content_id' => $data['level_content_id'] ?? null,
            'free_content' => $data['free_content'] ?? null,
            'activity_name' => $data['activity_name'],
            'type' => $data['type'],
            'links' => $data['links'] ?? null,
            'sort_order' => $data['sort_order'],
        ]);

        $this->syncStudyProgramWeekActivityStudyMaterial($activity, $studyMaterialFile);
        (new StudyProgramReplicationService())->propagateWeekActivityCreated($activity);

        return $activity->fresh(['studyProgramWeek.studyProgram.languageLevel.language', 'levelContent', 'media']);
    }

    public function updateStudyProgramWeekActivity(
        StudyProgramWeekActivity $activity,
        array $data,
        ?UploadedFile $studyMaterialFile = null
    ): StudyProgramWeekActivity
    {
        $activity->update([
            'level_content_id' => $data['level_content_id'] ?? null,
            'free_content' => $data['free_content'] ?? null,
            'activity_name' => $data['activity_name'],
            'type' => $data['type'],
            'links' => $data['links'] ?? null,
            'sort_order' => $data['sort_order'],
        ]);

        $this->syncStudyProgramWeekActivityStudyMaterial($activity, $studyMaterialFile);
        (new StudyProgramReplicationService())->propagateWeekActivityUpdated($activity);

        return $activity->fresh(['studyProgramWeek.studyProgram.languageLevel.language', 'levelContent', 'media']);
    }

    public function deleteStudyProgramWeekActivity(StudyProgramWeekActivity $activity): void
    {
        $activity->delete();
    }

    protected function syncStudyProgramWeekActivityStudyMaterial(
        StudyProgramWeekActivity $activity,
        ?UploadedFile $studyMaterialFile = null
    ): void
    {
        if (!$studyMaterialFile) {
            return;
        }

        $activity
            ->addMedia($studyMaterialFile)
            ->toMediaCollection('study-materials');
    }
}
