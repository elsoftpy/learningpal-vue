<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Lang;

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
        return [
            'language_id' => Language::query()->inRandomOrder()->first()?->id ?? Language::factory()->create()->id,
            'language_level_id' => LanguageLevel::query()->inRandomOrder()->first()?->id ?? LanguageLevel::factory()->create()->id,
            'name' => $this->faker->sentence(3),
            'chat_room_link' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement(['active', 'disabled']),
        ];
    }
}
