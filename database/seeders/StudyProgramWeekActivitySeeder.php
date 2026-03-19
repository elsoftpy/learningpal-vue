<?php

namespace Database\Seeders;

use App\Enums\StudyProgramActivityTypeEnum;
use App\Models\LevelContent;
use App\Models\StudyProgramWeek;
use App\Models\StudyProgramWeekActivity;
use Illuminate\Database\Seeder;

class StudyProgramWeekActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activityBlueprint = [
            ['type' => StudyProgramActivityTypeEnum::EXERCISE->value, 'activity_name' => 'Vocabulary practice', 'free_content' => null, 'links' => null],
            ['type' => StudyProgramActivityTypeEnum::VIDEO->value, 'activity_name' => 'Listening video', 'free_content' => null, 'links' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ|https://www.youtube.com/watch?v=aqz-KE-bpKQ'],
            ['type' => StudyProgramActivityTypeEnum::ACTIVITY->value, 'activity_name' => 'Guided speaking activity', 'free_content' => null, 'links' => null],
            ['type' => StudyProgramActivityTypeEnum::PRODUCTION->value, 'activity_name' => 'Student production assignment', 'free_content' => 'Create a short oral or written production based on the week topic.', 'links' => null],
        ];

        $studyProgramWeeks = StudyProgramWeek::query()->with('studyProgram.languageLevel')->get();

        foreach ($studyProgramWeeks as $studyProgramWeek) {
            $languageLevelId = $studyProgramWeek->studyProgram->language_level_id;

            $levelContents = LevelContent::query()
                ->where('language_level_id', $languageLevelId)
                ->orderBy('id')
                ->get();

            if ($levelContents->isEmpty()) {
                for ($index = 1; $index <= 4; $index++) {
                    LevelContent::query()->create([
                        'language_level_id' => $languageLevelId,
                        'content' => sprintf('Week %d Topic %d', $studyProgramWeek->week_number, $index),
                    ]);
                }

                $levelContents = LevelContent::query()
                    ->where('language_level_id', $languageLevelId)
                    ->orderBy('id')
                    ->get();
            }

            foreach ($activityBlueprint as $index => $activity) {
                $levelContentId = $activity['type'] === StudyProgramActivityTypeEnum::PRODUCTION->value
                    ? null
                    : $levelContents->get($index % max($levelContents->count(), 1))?->id;

                StudyProgramWeekActivity::query()->updateOrCreate(
                    [
                        'study_program_week_id' => $studyProgramWeek->id,
                        'sort_order' => $index + 1,
                    ],
                    [
                        'level_content_id' => $levelContentId,
                        'free_content' => $activity['free_content'],
                        'activity_name' => $activity['activity_name'],
                        'type' => $activity['type'],
                        'links' => $activity['links'],
                    ]
                );
            }
        }
    }
}
