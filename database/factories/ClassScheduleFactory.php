<?php

namespace Database\Factories;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::query()->inRandomOrder()->first()?->id ?? Course::factory(),
            'name' => $this->faker->word(),
            'schedule_month' => Carbon::instance($this->faker->dateTimeBetween('now', '+3 months'))->startOfMonth(),
            'feedback' => $this->faker->optional()->sentence(),
        ];
    }
}
