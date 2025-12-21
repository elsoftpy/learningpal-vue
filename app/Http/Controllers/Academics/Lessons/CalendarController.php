<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Settings\CourseService;
use App\Services\Academics\Settings\TeacherService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $sessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->whereIn('status', [
                ClassScheduleStatusEnum::SCHEDULED->value, 
                ClassScheduleStatusEnum::REPROGRAMED->value,
            ]); 

        $user = $request->user();
        $sessionsQuery = (new TeacherService())->applyTeacherCoursesFilter(
            user: $user,
            query: $sessionsQuery,
            relation: 'classSchedule'
        );
        
        $sessions = $sessionsQuery->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function scheduledCalendarCourses(CalendarSessionRequest $request)
    {
        $courses = Course::query()
            ->whereHas('classSchedules', function ($query) use ($request) {
                $query->whereBetween('schedule_month', [
                    $request->start_date, 
                    $request->end_date,
                ]);
            })
            ->get(); 

        $calendars = $courses->map(function ($course) {
            $displayName =  (new CourseService())->getCourseDisplayName($course);

            if ($course->id === 1){
                return [
                    'course_id' => $course->id,
                    $displayName => [
                        'lightColors' => [
                            'main' => "#10b981",
                            'container' => "#d1fae5",
                            'onContainer' => "#831843",
                        ],
                        'darkColors' => [
                            'main' => "#34d399",
                            'container' => "#065f46",
                            'onContainer' => "#d1fae5",    
                        ],
                    ]
                ];
            }

            return [
                $displayName => [
                    'course_id' => $course->id,
                    'lightColors' => [
                        'main' => "#2563eb",
                        'container' => "#dbeafe",
                        'onContainer' => "#065f46",
                    ],
                    'darkColors' => [
                        'main' => "#3b82f6",
                        'container' => "#1e3a8a",
                        'onContainer' => "#dbeafe",    
                    ],
                ]
            ];
        });

        return response()->json([
            'calendars' => $calendars,
        ]);
    }

    public function ongoingAndPendingSessions(Request $request)
    {
        $ongoingAndPendingSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereIn('status', [
                ClassScheduleStatusEnum::ONGOING->value, 
                ClassScheduleStatusEnum::PENDING->value
            ])
            ->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'ongoing_and_pending_sessions' => $ongoingAndPendingSessions,
        ]);
    }
}