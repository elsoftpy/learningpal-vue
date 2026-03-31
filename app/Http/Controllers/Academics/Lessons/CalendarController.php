<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Services\Academics\Lessons\CalendarService;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Settings\TeacherService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $user = $request->user();
        $visibleCourseIds = $this->resolveVisibleCourseIds($request);

        $startDate = $request->start_date->copy()->startOfDay();
        $endDate = $request->end_date->copy()->endOfDay();

        $sessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
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
        $calendars = $calendarService->calendarsColorsScheme($request, $this->resolveVisibleCourseIds($request));

        return response()->json([
            'calendars' => $calendars,
        ]);
    }

    public function ongoingAndPendingSessions(Request $request)
    {
        $visibleCourseIds = $this->resolveVisibleCourseIds($request);

        $ongoingAndPendingSessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
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

    private function resolveVisibleCourseIds(Request $request): ?array
    {
        $user = $request->user();

        if ($user?->can('view all students')) {
            return null;
        }

        if ($user?->profile?->teacher) {
            return (new TeacherService())->assignedCoursesArray($user->profile->teacher);
        }

        if ($user?->profile?->student) {
            return $user->profile->student->courses()->pluck('courses.id')->all();
        }

        return [];
    }
}
