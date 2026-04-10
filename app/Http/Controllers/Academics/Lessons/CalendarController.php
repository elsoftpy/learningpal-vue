<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Services\Academics\Lessons\CalendarService;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Authorization\CourseVisibilityService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $visibleCourseIds = (new CourseVisibilityService())->visibleCourseIdsForUser($request->user());

        $startDate = $request->start_date->copy()->startOfDay();
        $endDate = $request->end_date->copy()->endOfDay();

        $sessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereHas('classSchedule.course')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('session_date', [$startDate, $endDate])
                    ->orWhereBetween('rescheduled_date', [$startDate, $endDate]);
            })
            ->when($visibleCourseIds !== null, function ($q) use ($visibleCourseIds) {
                $q->whereHas('classSchedule', function ($q2) use ($visibleCourseIds) {
                    $q2->whereIn('course_id', $visibleCourseIds);
                });
            })
            ->get()
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
        $calendars = $calendarService->calendarsColorsScheme(
            $request,
            (new CourseVisibilityService())->visibleCourseIdsForUser($request->user())
        );

        return response()->json([
            'calendars' => $calendars,
        ]);
    }

    public function ongoingAndPendingSessions(Request $request)
    {
        $visibleCourseIds = (new CourseVisibilityService())->visibleCourseIdsForUser($request->user());

        $ongoingAndPendingSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereHas('classSchedule.course')
            ->whereIn('status', [
                ClassScheduleStatusEnum::ONGOING->value, 
                ClassScheduleStatusEnum::PENDING->value
            ])
            ->when($visibleCourseIds !== null, function ($query) use ($visibleCourseIds) {
                $query->whereHas('classSchedule', function ($q) use ($visibleCourseIds) {
                    $q->whereIn('course_id', $visibleCourseIds);
                });
            })
            ->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'ongoing_and_pending_sessions' => $ongoingAndPendingSessions,
        ]);
    }

}
