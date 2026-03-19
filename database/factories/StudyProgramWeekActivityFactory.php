<?php

namespace Database\Factories;

use App\Enums\StudyProgramActivityTypeEnum;
use App\Models\LevelContent;
use App\Models\StudyProgramWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyProgramWeekActivity>
 */
class StudyProgramWeekActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $week = StudyProgramWeek::query()->with('studyProgram.languageLevel')->inRandomOrder()->first() ?? StudyProgramWeek::factory()->create();
        $languageLevelId = $week->studyProgram->language_level_id;
        $levelContent = LevelContent::query()
            ->where('language_level_id', $languageLevelId)
            ->inRandomOrder()
            ->first();

        if (! $levelContent) {
            $levelContent = LevelContent::query()->create([
                'language_level_id' => $languageLevelId,
                'content' => sprintf('Topic %d', $this->faker->unique()->numberBetween(1, 999)),
            ]);
        }

        $type = $this->faker->randomElement(StudyProgramActivityTypeEnum::values());
        $useLevelContent = $this->faker->boolean(75);
        $freeContent = $this->faker->boolean(30) ? $this->faker->sentence() : null;

        if (! $useLevelContent && ! $freeContent) {
            $freeContent = $this->faker->sentence();
        }

        return [
            'study_program_week_id' => $week->id,
            'level_content_id' => $useLevelContent ? $levelContent->id : null,
            'free_content' => $freeContent,
            'activity_name' => ucfirst($type).' '.$this->faker->words(2, true),
            'type' => $type,
            'links' => $type === StudyProgramActivityTypeEnum::VIDEO->value ? $this->faker->url() : null,
            'sort_order' => $this->faker->numberBetween(1, 5),
        ];
    }
}
