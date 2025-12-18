<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarSessionRequest;
use App\Models\ClassScheduleDetail;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Settings\CourseService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function calendarSessions(CalendarSessionRequest $request)
    {
        $sessions = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereBetween('session_date', [
                $request->start_date, 
                $request->end_date,
            ])
            ->whereIn('status', [
                ClassScheduleStatusEnum::SCHEDULED->value, 
                ClassScheduleStatusEnum::RESCHEDULED->value,
            ])
            ->get()
            ->map(function (ClassScheduleDetail $detail) {
                return (new ClassScheduleDetailService())->sessionData($detail);
            });
        
        return response()->json([
            'sessions' => $sessions,
        ]);
    }
}
