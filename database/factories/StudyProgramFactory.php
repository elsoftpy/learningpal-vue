<?php

namespace Database\Factories;

use App\Enums\StudyProgramStatusEnum;
use App\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyProgram>
 */
class StudyProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languageLevel = LanguageLevel::query()
            ->with('language')
            ->whereDoesntHave('studyProgram')
            ->inRandomOrder()
            ->first()
            ?? LanguageLevel::factory()->create();
        $languageName = $languageLevel->relationLoaded('language') ? $languageLevel->language?->name : $languageLevel->language()->value('name');

        return [
            'language_level_id' => $languageLevel->id,
            'title' => trim(sprintf('%s %s Study Program', $languageName ?? 'Language', $languageLevel->level ?? 'Level')),
            'status' => StudyProgramStatusEnum::ACTIVE->value,
        ];
    }
}
