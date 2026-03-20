<?php

namespace Database\Seeders;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
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

        if ($schedules->isEmpty()) {
            return;
        }

        $plannedDetails = collect();

        foreach ($schedules as $schedule) {
            $monthStart = CarbonImmutable::parse($schedule->schedule_month)->startOfMonth();
            $monthEnd = $monthStart->endOfMonth();

            $targetWeekday = $schedule->course_id % 2 === 0
                ? CarbonInterface::TUESDAY
                : CarbonInterface::MONDAY;

            $sessionDate = $monthStart;
            while ($sessionDate->dayOfWeek !== $targetWeekday) {
                $sessionDate = $sessionDate->addDay();
            }

            $order = 1;
            while ($sessionDate->lessThanOrEqualTo($monthEnd)) {
                $plannedDetails->push([
                    'class_schedule_id' => $schedule->id,
                    'course_id' => $schedule->course_id,
                    'order' => $order,
                    'session_date' => $sessionDate,
                    'schedule' => $schedule,
                ]);

                $sessionDate = $sessionDate->addWeek();
                $order++;
            }
        }

        $plannedDetails = $plannedDetails
            ->sortBy([
                ['session_date', 'asc'],
                ['class_schedule_id', 'asc'],
                ['order', 'asc'],
            ])
            ->values();

        $noRecordCount = (int) round($plannedDetails->count() * 0.2);
        $noRecordIndexes = collect(range(0, max($noRecordCount - 1, -1)))->flip();

        $keptOrdersBySchedule = [];

        foreach ($plannedDetails as $index => $plannedDetail) {
            $schedule = $plannedDetail['schedule'];
            $order = $plannedDetail['order'];
            $sessionDate = $plannedDetail['session_date'];

            $levelContents = $schedule->course?->languageLevel?->levelContents;
            $levelContentCount = max($levelContents?->count() ?? 0, 1);
            $topic = $levelContents?->values()->get($index % $levelContentCount)?->content
                ?? sprintf('Week %d communication topic', $order);

            $startHour = 8 + (($plannedDetail['course_id'] + $order) % 5);
            $startTime = $sessionDate->setTime($startHour, 0);
            $endTime = $startTime->addMinutes(45);

            $isNoRecordSlot = $noRecordIndexes->has($index);
            $isReprogrammed = $isNoRecordSlot && (($index + 1) % 2 === 0);

            ClassScheduleDetail::query()->updateOrCreate(
                [
                    'class_schedule_id' => $plannedDetail['class_schedule_id'],
                    'order' => $order,
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

            if (!isset($keptOrdersBySchedule[$plannedDetail['class_schedule_id']])) {
                $keptOrdersBySchedule[$plannedDetail['class_schedule_id']] = [];
            }

            $keptOrdersBySchedule[$plannedDetail['class_schedule_id']][] = $order;
        }

        foreach ($schedules as $schedule) {
            $keptOrders = $keptOrdersBySchedule[$schedule->id] ?? [];
            ClassScheduleDetail::query()
                ->where('class_schedule_id', $schedule->id)
                ->when(
                    !empty($keptOrders),
                    fn ($query) => $query->whereNotIn('order', $keptOrders),
                    fn ($query) => $query->whereNotNull('id')
                )
                ->delete();
        }
    }
}
