<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Enums\ProfileTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $fullName = "{$firstName} {$lastName}";
        return [
            'type' => ProfileTypeEnum::PERSON->value,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'personal_id' => fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'gender' => fake()->randomElement(GenderEnum::values()),
        ];
    }
}
