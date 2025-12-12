<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            'English',
            'Spanish',
            'Portuguese',
            'French',
            'German',
            'Italian',
            'Chinese',
            'Japanese',
            'Russian',
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(['name' => $language]);
        }
    }
}
