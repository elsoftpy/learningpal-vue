<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassScheduleDetailRequest;
use App\Models\ClassScheduleDetail;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Utilities\ResponseService;

class ClassScheduleDetailController extends Controller
{
    
    public function update(ClassScheduleDetailRequest $request, ClassScheduleDetail $detail)
    {
        (new CourseVisibilityService())->authorizeCourseId($request->user(), $detail->classSchedule?->course_id);

        $validated = $request->validated();
        $manualStatus = $validated['status'] ?? null;

        $detail->fill($validated);

        $hasRescheduleData = $request->filled('rescheduled_date')
            && $request->filled('rescheduled_start_time')
            && $request->filled('rescheduled_end_time');

        if ($hasRescheduleData) {
            $detail->reschedule_count = (int) $detail->reschedule_count + 1;
            $detail->rescheduled_estimated_duration_minutes = $request
                ->rescheduled_start_time->diffInMinutes($request->rescheduled_end_time);

            // If reschedule data is provided, mark session as reprogramed unless explicitly set to another status.
            $detail->status = $manualStatus ?: ClassScheduleStatusEnum::REPROGRAMED->value;
        } elseif ($manualStatus) {
            $detail->status = $manualStatus;
        }

        $detail->save();

        return ResponseService::success(
            message: __('Class schedule detail updated successfully.'),
        );
    }

    public function destroy(ClassScheduleDetail $detail)
    {
        (new CourseVisibilityService())->authorizeCourseId(request()->user(), $detail->classSchedule?->course_id);

        $detail->delete();

        return ResponseService::success(
            message: __('Class schedule detail deleted successfully.')
        );
    }
}
