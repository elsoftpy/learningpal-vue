<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassScheduleDetailRequest;
use App\Models\ClassScheduleDetail;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassScheduleDetailController extends Controller
{
    
    public function update(ClassScheduleDetailRequest $request, ClassScheduleDetail $detail)
    {
        DB::transaction(function () use ($request, $detail) {
            $detail->update($request->validated());

            if ($request->has('rescheduled_date')) {
                $detail->reschedule_count = (int) $detail->reschedule_count + 1;
                $detail->rescheduled_estimated_duration_minutes = $request
                    ->rescheduled_start_time->diffInMinutes($request->rescheduled_end_time);
                $detail->status = ClassScheduleStatusEnum::REPROGRAMED->value;
                $detail->save();
            
            }
        });

        return ResponseService::success(
            message: __('Class schedule detail updated successfully.'),
        );
    }

    public function destroy(ClassScheduleDetail $detail)
    {
        $detail->delete();

        return ResponseService::success(
            message: __('Class schedule detail deleted successfully.')
        );
    }
}
