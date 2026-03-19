<?php

namespace Database\Seeders;

use App\Enums\StudyProgramStatusEnum;
use App\Models\LanguageLevel;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languageLevels = LanguageLevel::query()->with('language')->get();

        foreach ($languageLevels as $languageLevel) {
            $languageName = $languageLevel->language?->name ?? 'Language';
            $levelName = $languageLevel->level ?? $languageLevel->description;

            StudyProgram::query()->firstOrCreate(
                ['language_level_id' => $languageLevel->id],
                [
                    'title' => trim(sprintf('%s %s Study Program', $languageName, $levelName)),
                    'status' => StudyProgramStatusEnum::ACTIVE->value,
                ]
            );
        }
    }
}
