<?php

namespace App\Services\Academics\Lessons;

use App\Models\ClassSchedule;
use App\Services\Academics\Settings\CourseService;
use App\Services\Utilities\DateTimeService;

class ClassScheduleService
{
    public function createClassSchedule(array $data): ClassSchedule
    {
        $detail = $data['details'] ?? null;
        unset($data['details']);

        $classSchedule = ClassSchedule::create($data);

        if (isset($detail) && is_array($detail)) {
            $order = 1;
            foreach ($detail as $detailData) {
                if (isset($detailData['start_time']) && isset($detailData['end_time'])) {
                    $estimatedDuration = $detailData['start_time']->diffInMinutes($detailData['end_time']);
                    $detailData['estimated_duration_minutes'] = $estimatedDuration;
                }
                $detailData['order'] = $order++;
                $classSchedule->details()->create($detailData);
            }
        }
        return $classSchedule;
    }

    public function updateClassSchedule(ClassSchedule $classSchedule, array $data): ClassSchedule
    {
        // We don't handle details update here
        unset($data['details']);

        $classSchedule->update($data);

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