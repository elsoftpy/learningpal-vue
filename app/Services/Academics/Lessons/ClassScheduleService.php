<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassSchedule;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\DateTimeService;

class ClassScheduleService
{
    public function createClassSchedule(array $data, array $detail): ClassSchedule
    {
        $classSchedule = ClassSchedule::create($data);
        if (isset($detail) && is_array($detail)) {
            foreach ($detail as $detailData) {
                $classSchedule->details()->create($detailData);
            }
        }
        return $classSchedule;
    }

    public function classScheduleData(ClassSchedule $classSchedule)
    {
        $course = $classSchedule->course;
        $courseName = (new CourseService())->getCourseDisplayName($course);
        return [
            'id' => $classSchedule->id,
            'name' => $classSchedule->name,
            'schedule_month' => $classSchedule->schedule_month,
            'display_schedule_month' => DateTimeService::formatDateMonthYear($classSchedule->schedule_month),
            'course_id' => $course->id,
            'course' => $courseName,
            'details' => $classSchedule->details()
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->get()
                ->map(function ($detail) {
                    return (new ClassScheduleDetailService())->classScheduleDetailData($detail);
                }),
        ];
    }
}