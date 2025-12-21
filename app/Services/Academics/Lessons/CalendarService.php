<?php

namespace App\Services\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\CalendarColorPalette;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CalendarService
{
    protected function scheduledSessionsQuery(Carbon $startDate, Carbon $endDate): Builder {
    
        return ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $startDate->startOfDay(), 
                $endDate->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::SCHEDULED->value);
    }

    protected function reprogramedSessionsQuery(Carbon $startDate, Carbon $endDate): Builder {
    
        return ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('rescheduled_date', [
                $startDate->startOfDay(), 
                $endDate->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::REPROGRAMED->value);
    }

    public function calendarsColorsScheme(Request $request): array
    {
        $scheduledSessions = $this->scheduledSessionsQuery(
            $request->start_date,
            $request->end_date
        );

        $reprogramedSessions = $this->reprogramedSessionsQuery(
            $request->start_date,
            $request->end_date
        );
          
        $coursesIds = $scheduledSessions->unionAll($reprogramedSessions)
           ->get()
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
        });
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