<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassProgramDetail;
use App\Models\ClassScheduleDetail;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;

class ClassScheduleDetailService
{
    public function classScheduleDetailData(ClassScheduleDetail $detail): array
    {
        $classRecordId = $detail->classRecord?->id;

        return [
            'id' => $detail->id,
            'class_schedule_id' => $detail->class_schedule_id,
            'session_date' => DateTimeService::formatDate($detail->session_date),
            'start_time' => DateTimeService::formatTime($detail->start_time),
            'end_time' => DateTimeService::formatTime($detail->end_time),
            'estimated_duration_minutes' => $detail->estimated_duration_minutes,
            'rescheduled_date' => DateTimeService::formatDate($detail?->rescheduled_date),
            'rescheduled_start_time' => DateTimeService::formatTime($detail?->rescheduled_start_time),
            'rescheduled_end_time' => DateTimeService::formatTime($detail?->rescheduled_end_time),
            'rescheduled_estimated_duration_minutes' => $detail->rescheduled_estimated_duration_minutes,
            'reschedule_count' => $detail->reschedule_count,
            'topic' => $detail->topic,
            'activity' => $detail->activity,
            'order' => $detail->order,
            'status' => $detail->status,
            'display_status' => ucfirst(__( $detail->status )),
            'is_completed' => $detail->status === 'completed',
            'class_record_id' => $classRecordId,
            'has_class_record' => (bool) $classRecordId,
        ];
    }

    public function sessionData(ClassScheduleDetail $detail): array
    {
        $course = $detail->classSchedule?->course;

        return [
            'id' => $detail->id,
            'class_schedule_id' => $detail->class_schedule_id,
            'date' => $detail->session_date?->toDateString(),
            'start_time' => DateTimeService::formatTime($detail->start_time),
            'end_time' => DateTimeService::formatTime($detail->end_time),
            'rescheduled_date' => $detail->rescheduled_date?->toDateString(),
            'rescheduled_start_time' => DateTimeService::formatTime($detail?->rescheduled_start_time),
            'rescheduled_end_time' => DateTimeService::formatTime($detail?->rescheduled_end_time),
            'course_id' => $course?->id,
            'display_course' => $course ? (new CourseService())->getCourseDisplayName($course) : '',
            'chat_room_url' => $course?->chat_room_link,
            'status' => $detail->status,
            'display_status' => ucfirst(__( $detail->status )),
        ];
    }   
}
