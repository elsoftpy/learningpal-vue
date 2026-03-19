<?php

namespace Database\Seeders;

use App\Enums\StudyProgramStatusEnum;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use Illuminate\Database\Seeder;

class StudyProgramWeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studyPrograms = StudyProgram::query()->get();

        foreach ($studyPrograms as $studyProgram) {
            for ($weekNumber = 1; $weekNumber <= 4; $weekNumber++) {
                StudyProgramWeek::query()->updateOrCreate(
                    [
                        'study_program_id' => $studyProgram->id,
                        'week_number' => $weekNumber,
                    ],
                    [
                        'title' => sprintf('%s Week %d', $studyProgram->title, $weekNumber),
                        'status' => StudyProgramStatusEnum::ACTIVE->value,
                    ]
                );
            }
        }
    }
}
