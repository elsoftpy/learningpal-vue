<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LanguageLevel>
 */
class LanguageLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_id' => Language::query()->inRandomOrder()->first()?->id ?? Language::factory()->create()->id,
            'description' => $this->faker->randomElement(['Beginner', 'Elementary', 'Intermediate', 'Upper Intermediate', 'Advanced', 'Proficient']),
            'level' => $this->faker->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'status' => $this->faker->randomElement(['active', 'disabled']),
        ];
    }
}
