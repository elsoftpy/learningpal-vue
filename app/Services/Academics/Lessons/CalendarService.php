<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\CalendarColorPalette;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CalendarService
{
    protected function sessionsQuery(Carbon $startDate, Carbon $endDate): Builder {
        return ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('session_date', [
                    $startDate->startOfDay(),
                    $endDate->endOfDay(),
                ])->orWhereBetween('rescheduled_date', [
                    $startDate->startOfDay(),
                    $endDate->endOfDay(),
                ]);
            });
    }

    public function calendarsColorsScheme(Request $request, ?array $visibleCourseIds = null): array
    {
        $sessions = $this->sessionsQuery(
            $request->start_date,
            $request->end_date
        );

        if ($visibleCourseIds !== null) {
            $sessions->whereHas('classSchedule', function ($query) use ($visibleCourseIds) {
                $query->whereIn('course_id', $visibleCourseIds);
            });
        }

        $coursesIds = $sessions->get()
            ->pluck('classSchedule.course.id')
            ->unique()
            ->toArray();

        $courses = Course::query()
            ->whereIn('id', $coursesIds)
            ->get(); 

        $caledarColors = $this->calendarColors($coursesIds);
        $courseService = new CourseService();
        
        return $courses->map(function ($course) use ($caledarColors, $courseService) {
            $displayName =  $courseService->getCourseDisplayName($course);

            return [
                $displayName => [
                    'course_id' => $course->id,
                    'lightColors' => $caledarColors[$course->id]['light'],
                    'darkColors' => $caledarColors[$course->id]['dark'],
                ]
            ];
        })->toArray();
    }

    protected function calendarColors(array $courseIds)
    {
        $colors = [];
        $colorPalette = CalendarColorPalette::getCalendarColorPalette();

        foreach ($courseIds as $index => $courseId) {
            $paletteIndex = $index % count($colorPalette);
            $colors[$courseId] = $colorPalette[$paletteIndex];
        }

        return $colors;
    }
}
