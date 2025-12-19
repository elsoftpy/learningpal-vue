<?php

namespace App\Services\Academics\Settings;

use App\Models\Course;
use Illuminate\Support\Collection;

class CourseService
{
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
        return $courses->map(function($course) {
            return $this->getCourseDisplayName($course);
        })->toArray();
    }
}