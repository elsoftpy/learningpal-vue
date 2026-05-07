<?php

namespace App\Services\Academics\Settings;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseService
{
    public function createCourse(array $data, User $user): Course
    {
        return DB::transaction(function () use ($data, $user) {
            $course = Course::query()->create($data);

            (new StudyProgramReplicationService)->replicateToCourse($course, $user);

            return $course->fresh(['language', 'languageLevel']);
        });
    }

    public function updateCourse(Course $course, array $data, User $user): Course
    {
        return DB::transaction(function () use ($course, $data, $user) {
            $oldLanguageLevelId = $course->language_level_id;

            $course->update($data);

            if (isset($data['language_level_id']) && (int) $data['language_level_id'] !== (int) $oldLanguageLevelId) {
                $fresh = $course->fresh(['students']);

                (new StudyProgramReplicationService)->replicateToCourse($fresh, $user);

                $enrollmentService = new DistanceActivityEnrollmentService;

                foreach ($fresh->students as $student) {
                    $enrollmentService->syncStudentEnrollments($student, [$course->id]);
                }
            }

            return $course->fresh(['language', 'languageLevel']);
        });
    }

    public function courseData(Course $course)
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'language_id' => $course->language_id,
            'language_name' => $course->language->name,
            'language_level_id' => $course->language_level_id,
            'language_level' => $course->languageLevel->level,
            'chat_room_link' => $course->chat_room_link,
            'status' => $course->status,
            'display_status' => ucfirst(__($course->status)),
        ];
    }

    public function getCourseDisplayName(Course $course): string
    {
        return $course->name.
            ' - '.
            $course->languageLevel?->level.
            ' - '.
            $course->language?->name;
    }

    public function getCoursesDisplayNames(Collection $courses): array
    {
        return $courses->map(function ($course) {
            return $this->getCourseDisplayName($course);
        })->toArray();
    }
}
