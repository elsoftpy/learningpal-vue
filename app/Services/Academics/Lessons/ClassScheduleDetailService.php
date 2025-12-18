<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassProgramDetail;
use App\Models\ClassScheduleDetail;

class ClassScheduleDetailService
{
    public function classScheduleDetailData(ClassScheduleDetail $detail)
    {
        return [
            'id' => $detail->id,
            'class_schedule_id' => $detail->class_schedule_id,
            'session_date' => $detail->session_date?->format(match(app()->getLocale()) {
                'es', 'pt' => 'd/m/Y',
                'en' => 'm-d-Y',
                default => 'Y-m-d',
            }) ?? null,
            'start_time' => $detail->start_time?->format(match(app()->getLocale()) {
                'es', 'pt' => 'H:i',
                'en' => 'h:i A',
                default => 'H:i',
            }) ?? null,
            'end_time' => $detail->end_time?->format(match(app()->getLocale()) {
                'es', 'pt' => 'H:i',
                'en' => 'h:i A',
                default => 'H:i',
            }) ?? null,
            'estimated_duration_minutes' => $detail->estimated_duration_minutes,
            'rescheduled_date' => $detail->rescheduled_date?->format(match(app()->getLocale()) {
                'es', 'pt' => 'd/m/Y',
                'en' => 'm-d-Y',
                default => 'Y-m-d',
            }) ?? null,
            'rescheduled_start_time' => $detail->rescheduled_start_time?->format(match(app()->getLocale()) {
                'es', 'pt' => 'H:i',
                'en' => 'h:i A',
                default => 'H:i',
            }) ?? null,
            'rescheduled_end_time' => $detail->rescheduled_end_time?->format(match(app()->getLocale()) {
                'es', 'pt' => 'H:i',
                'en' => 'h:i A',
                default => 'H:i',
            }) ?? null,
            'rescheduled_estimated_duration_minutes' => $detail->rescheduled_estimated_duration_minutes,
            'reschedule_count' => $detail->reschedule_count,
            'topic' => $detail->topic,
            'activity' => $detail->activity,
            'order' => $detail->order,
            'status' => $detail->status,
            'display_status' => ucfirst(__( $detail->status )),
        ];
    }
}