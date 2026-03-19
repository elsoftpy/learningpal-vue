<?php

namespace Database\Factories;

use App\Enums\StudyProgramStatusEnum;
use App\Models\Course;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DistanceActivity>
 */
class DistanceActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::query()->inRandomOrder()->first() ?? Course::factory()->create();
        $studyProgramWeek = StudyProgramWeek::query()
            ->whereHas('studyProgram', fn ($query) => $query->where('language_level_id', $course->language_level_id))
            ->inRandomOrder()
            ->first();

        if (! $studyProgramWeek) {
            $studyProgram = StudyProgram::query()->firstOrCreate(
                ['language_level_id' => $course->language_level_id],
                ['title' => sprintf('%s Study Program', $course->name), 'status' => StudyProgramStatusEnum::ACTIVE->value]
            );

            $studyProgramWeek = StudyProgramWeek::factory()->create([
                'study_program_id' => $studyProgram->id,
            ]);
        }

        return [
            'course_id' => $course->id,
            'study_program_week_id' => $studyProgramWeek->id,
            'teacher_id' => $this->faker->boolean(70) ? (Teacher::query()->inRandomOrder()->first()?->id ?? Teacher::factory()->create()->id) : null,
            'user_id' => User::query()->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'title' => $studyProgramWeek->title,
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
