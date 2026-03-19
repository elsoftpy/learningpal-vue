<?php

namespace App\Services\Academics\Settings;

use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityDetail;
use App\Models\User;

class StudyProgramReplicationService
{
    public function replicateToCourse(Course $course, User $user): void
    {
        $course->loadMissing([
            'teachers',
            'languageLevel.studyProgram.weeks.activities',
        ]);

        $studyProgram = $course->languageLevel?->studyProgram;
        $teacherId = $course->teachers->first()?->id;

        if (! $studyProgram) {
            return;
        }

        foreach ($studyProgram->weeks->sortBy('week_number') as $week) {
            $distanceActivity = DistanceActivity::query()->create([
                'course_id' => $course->id,
                'study_program_week_id' => $week->id,
                'teacher_id' => $teacherId,
                'user_id' => $user->id,
                'title' => $week->title,
                'comments' => null,
            ]);

            foreach ($week->activities->sortBy('sort_order') as $activity) {
                DistanceActivityDetail::query()->create([
                    'distance_activity_id' => $distanceActivity->id,
                    'study_program_week_activity_id' => $activity->id,
                    'content_id' => $activity->level_content_id,
                    'free_content' => $activity->free_content,
                    'activity' => $activity->activity_name,
                    'type' => $activity->type->value,
                    'links' => $activity->links,
                    'file_path' => null,
                    'file_name' => null,
                ]);
            }
        }
    }
}
