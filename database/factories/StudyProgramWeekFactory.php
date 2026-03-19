<?php

namespace Database\Factories;

use App\Enums\StudyProgramStatusEnum;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyProgramWeek>
 */
class StudyProgramWeekFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $studyProgram = StudyProgram::query()->inRandomOrder()->first() ?? StudyProgram::factory()->create();
        $weekNumber = ((int) $studyProgram->weeks()->max('week_number')) + 1;

        return [
            'study_program_id' => $studyProgram->id,
            'week_number' => $weekNumber,
            'title' => sprintf('%s Week %d', $studyProgram->title, $weekNumber),
            'status' => StudyProgramStatusEnum::ACTIVE->value,
        ];
    }
}
