<?php

namespace Database\Seeders;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ClassScheduleDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = ClassSchedule::query()
            ->with(['course.languageLevel.levelContents'])
            ->orderBy('id')
            ->get();

        $sessionIndex = 0;

        foreach ($schedules as $schedule) {
            $sessionIndex++;

            preg_match('/(\d{4}-\d{2}-\d{2})$/', $schedule->name, $matches);
            $sessionDate = isset($matches[1])
                ? CarbonImmutable::parse($matches[1])
                : CarbonImmutable::parse($schedule->schedule_month)->startOfMonth();

            $startHour = 8 + (($schedule->course_id + $sessionIndex) % 5);
            $startTime = $sessionDate->setTime($startHour, 0);
            $endTime = $startTime->addMinutes(45);

            $isNoRecordSlot = $sessionIndex % 5 === 0;
            $isReprogrammed = $isNoRecordSlot && $sessionIndex % 10 === 0;

            $levelContents = $schedule->course?->languageLevel?->levelContents;
            $topic = $levelContents?->values()->get(($sessionIndex - 1) % max($levelContents->count(), 1))?->content
                ?? sprintf('Week %d communication topic', $sessionIndex);

            $detail = ClassScheduleDetail::query()->updateOrCreate(
                [
                    'class_schedule_id' => $schedule->id,
                    'order' => 1,
                ],
                [
                    'session_date' => $sessionDate->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'estimated_duration_minutes' => 45,
                    'rescheduled_date' => $isReprogrammed ? $sessionDate->addDays(2)->toDateString() : null,
                    'rescheduled_start_time' => $isReprogrammed ? $startTime->addDays(2) : null,
                    'rescheduled_end_time' => $isReprogrammed ? $endTime->addDays(2) : null,
                    'rescheduled_estimated_duration_minutes' => $isReprogrammed ? 45 : null,
                    'reschedule_count' => $isReprogrammed ? 1 : 0,
                    'topic' => $topic,
                    'activity' => $isNoRecordSlot
                        ? 'Lesson follow-up and schedule coordination'
                        : 'Grammar drill and speaking practice',
                    'status' => $isNoRecordSlot
                        ? ($isReprogrammed
                            ? ClassScheduleStatusEnum::REPROGRAMED->value
                            : ClassScheduleStatusEnum::PENDING->value)
                        : ClassScheduleStatusEnum::COMPLETED->value,
                ]
            );

            ClassScheduleDetail::query()
                ->where('class_schedule_id', $schedule->id)
                ->where('id', '!=', $detail->id)
                ->delete();
        }
    }
}
