<?php

namespace Database\Factories;

use App\Models\DistanceActivity;
use App\Models\LevelContent;
use App\Models\StudyProgramWeekActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DistanceActivityDetail>
 */
class DistanceActivityDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $distanceActivity = DistanceActivity::query()->with('studyProgramWeek.studyProgram')->inRandomOrder()->first() ?? DistanceActivity::factory()->create();
        $studyProgramWeekActivity = StudyProgramWeekActivity::query()
            ->where('study_program_week_id', $distanceActivity->study_program_week_id)
            ->inRandomOrder()
            ->first();

        if (! $studyProgramWeekActivity) {
            $studyProgramWeekActivity = StudyProgramWeekActivity::factory()->create([
                'study_program_week_id' => $distanceActivity->study_program_week_id,
            ]);
        }

        $contentId = $studyProgramWeekActivity->level_content_id;

        if (! $contentId) {
            $languageLevelId = $distanceActivity->studyProgramWeek?->studyProgram?->language_level_id;
            $contentId = $languageLevelId
                ? (LevelContent::query()->where('language_level_id', $languageLevelId)->inRandomOrder()->value('id')
                    ?? LevelContent::query()->create([
                        'language_level_id' => $languageLevelId,
                        'content' => 'Supplemental content',
                    ])->id)
                : null;
        }

        return [
            'distance_activity_id' => $distanceActivity->id,
            'study_program_week_activity_id' => $studyProgramWeekActivity->id,
            'content_id' => $studyProgramWeekActivity->level_content_id ?? $contentId,
            'free_content' => $studyProgramWeekActivity->free_content,
            'activity' => $studyProgramWeekActivity->activity_name,
            'type' => $studyProgramWeekActivity->type->value,
            'links' => $studyProgramWeekActivity->links,
            'file_path' => null,
            'file_name' => null,
        ];
    }
}
