<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Lessons\CalendarService;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Settings\CourseService;
use App\Services\Academics\Settings\TeacherService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $user = $request->user();
        
        $assignedCourses = [];
        if ($user->profile?->teacher) {
            $assignedCourses = (new TeacherService())->assignedCoursesArray($user->profile->teacher);
        }

        $scheduledSessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::SCHEDULED->value)
            ->when($user->cannot('view all students'), function ($q) use ($assignedCourses) {
                $q->whereHas('classSchedule', function ($q2) use ($assignedCourses) {
                    $q2->whereIn('course_id', $assignedCourses);
                });
            });

        $reprogramedSessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('rescheduled_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::REPROGRAMED->value)
            ->when($user->cannot('view all students'), function ($q) use ($assignedCourses) {
                $q->whereHas('classSchedule', function ($q2) use ($assignedCourses) {
                    $q2->whereIn('course_id', $assignedCourses);
                });
            });

        $completedSessionsQuery = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date->startOfDay(), 
                $request->end_date->endOfDay(),
            ])
            ->where('status', ClassScheduleStatusEnum::COMPLETED->value)
            ->when($user->cannot('view all students'), function ($q) use ($assignedCourses) {
                $q->whereHas('classSchedule', function ($q2) use ($assignedCourses) {
                    $q2->whereIn('course_id', $assignedCourses);
                });
            });

        $sessionsQuery = $scheduledSessionsQuery
            ->unionAll($reprogramedSessionsQuery)
            ->unionAll($completedSessionsQuery);
        
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
        $calendarService = new CalendarService();
        $calendars = $calendarService->calendarsColorsScheme($request);

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