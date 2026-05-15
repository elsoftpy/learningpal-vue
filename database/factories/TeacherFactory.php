<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory()->create()->id,
            'status' => $this->faker->randomElement(['active', 'disabled']),
        ];
    }
}
