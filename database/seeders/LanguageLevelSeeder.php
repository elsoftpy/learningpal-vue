<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\LanguageLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'language_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'description' => 'Beginner Level',
                'level' => 'A1',
            ],
            [
                'language_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'description' => 'Intermediate Level',
                'level' => 'B1',
            ],
            [
                'language_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'description' => 'Advanced Level',
                'level' => 'C1',
            ],
        ];

        foreach ($levels as $level) {
            LanguageLevel::firstOrCreate($level);
        }
    }
}
