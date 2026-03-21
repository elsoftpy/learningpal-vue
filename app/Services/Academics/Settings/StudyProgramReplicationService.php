<?php

namespace App\Services\Academics\Settings;

use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityDetail;
use App\Models\DistanceActivityDetailStudent;
use App\Models\DistanceActivityStudent;
use App\Models\StudyProgramWeek;
use App\Models\StudyProgramWeekActivity;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

    public function propagateWeekCreated(StudyProgramWeek $week): void
    {
        $week->loadMissing('studyProgram');

        $courses = Course::query()
            ->with(['distanceActivities', 'teachers.profile.user', 'students'])
            ->where('language_level_id', $week->studyProgram->language_level_id)
            ->get();

        foreach ($courses as $course) {
            $distanceActivity = $course->distanceActivities
                ->firstWhere('study_program_week_id', $week->id);

            if (! $distanceActivity) {
                $userId = $this->resolveDistanceActivityUserId($course);

                if (! $userId) {
                    continue;
                }

                $distanceActivity = DistanceActivity::query()->create([
                    'course_id' => $course->id,
                    'study_program_week_id' => $week->id,
                    'teacher_id' => $course->teachers->first()?->id,
                    'user_id' => $userId,
                    'title' => $week->title,
                    'comments' => null,
                ]);
            }

            $this->syncDistanceActivityStudents($course, $distanceActivity);
            $this->syncWeekActivitiesForDistanceActivity($week, $course, $distanceActivity);
        }
    }

    public function propagateWeekUpdated(StudyProgramWeek $week): void
    {
        $week->loadMissing('distanceActivities');

        DistanceActivity::query()
            ->where('study_program_week_id', $week->id)
            ->update([
                'title' => $week->title,
            ]);
    }

    public function propagateWeekActivityCreated(StudyProgramWeekActivity $activity): void
    {
        $activity->loadMissing('studyProgramWeek.distanceActivities.course.students');

        foreach ($activity->studyProgramWeek->distanceActivities as $distanceActivity) {
            $course = $distanceActivity->course;

            if (! $course) {
                continue;
            }

            $detail = $this->firstOrCreateDetail($distanceActivity, $activity);
            $this->syncDistanceActivityDetailStudents($course->students, $detail);
        }
    }

    public function propagateWeekActivityUpdated(StudyProgramWeekActivity $activity): void
    {
        DistanceActivityDetail::query()
            ->where('study_program_week_activity_id', $activity->id)
            ->update([
                'content_id' => $activity->level_content_id,
                'free_content' => $activity->free_content,
                'activity' => $activity->activity_name,
                'type' => $activity->type->value,
                'links' => $activity->links,
            ]);
    }

    protected function syncWeekActivitiesForDistanceActivity(
        StudyProgramWeek $week,
        Course $course,
        DistanceActivity $distanceActivity
    ): void {
        $week->loadMissing('activities');

        foreach ($week->activities->sortBy('sort_order') as $activity) {
            $detail = $this->firstOrCreateDetail($distanceActivity, $activity);
            $this->syncDistanceActivityDetailStudents($course->students, $detail);
        }
    }

    protected function firstOrCreateDetail(
        DistanceActivity $distanceActivity,
        StudyProgramWeekActivity $activity
    ): DistanceActivityDetail {
        return DistanceActivityDetail::query()->firstOrCreate(
            [
                'distance_activity_id' => $distanceActivity->id,
                'study_program_week_activity_id' => $activity->id,
            ],
            [
                'content_id' => $activity->level_content_id,
                'free_content' => $activity->free_content,
                'activity' => $activity->activity_name,
                'type' => $activity->type->value,
                'links' => $activity->links,
                'file_path' => null,
                'file_name' => null,
            ]
        );
    }

    protected function syncDistanceActivityStudents(Course $course, DistanceActivity $distanceActivity): void
    {
        $studentIds = $course->students->pluck('id')->all();

        foreach ($studentIds as $studentId) {
            DistanceActivityStudent::query()->firstOrCreate(
                [
                    'distance_activity_id' => $distanceActivity->id,
                    'student_id' => $studentId,
                ],
                [
                    'completed' => false,
                    'completed_at' => null,
                ]
            );
        }
    }

    protected function syncDistanceActivityDetailStudents(Collection $students, DistanceActivityDetail $detail): void
    {
        $studentIds = $students->pluck('id')->all();

        foreach ($studentIds as $studentId) {
            DistanceActivityDetailStudent::query()->firstOrCreate(
                [
                    'distance_activity_detail_id' => $detail->id,
                    'student_id' => $studentId,
                ],
                [
                    'completed' => false,
                    'completed_at' => null,
                ]
            );
        }
    }

    protected function resolveDistanceActivityUserId(Course $course): ?int
    {
        $authUserId = Auth::id();

        if ($authUserId) {
            return (int) $authUserId;
        }

        $existingUserId = $course->distanceActivities->first()?->user_id;

        if ($existingUserId) {
            return (int) $existingUserId;
        }

        $teacherUserId = $course->teachers
            ->pluck('profile.user.id')
            ->filter()
            ->first();

        return $teacherUserId ? (int) $teacherUserId : null;
    }
}
