<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languageLevel = LanguageLevel::query()->with('language')->inRandomOrder()->first() ?? LanguageLevel::factory()->create();

        return [
            'language_id' => $languageLevel->language_id ?? Language::query()->inRandomOrder()->first()?->id ?? Language::factory()->create()->id,
            'language_level_id' => $languageLevel->id,
            'name' => $this->faker->sentence(3),
            'chat_room_link' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement(['active', 'disabled']),
        ];
    }
}
