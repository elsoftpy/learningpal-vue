<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassScheduleDetail>
 */
class ClassScheduleDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classSchedule = ClassSchedule::query()->inRandomOrder()->first() ?? ClassSchedule::factory()->create();
        $day = $this->faker->numberBetween(1, 28);
        $sessionDate = Carbon::createFromDate(
            $classSchedule->schedule_month->year,
            $classSchedule->schedule_month->month,
            $day
        );
        $startHour = $this->faker->numberBetween(8, 10);
        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $sessionDate->format('Y-m-d') . ' ' . sprintf('%02d:00:00', $startHour)
        );
        $endTime = (clone $startTime)->modify('+1 hour');
        // calculate duration in minutes    
        $duration = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 60;

        $rescheduledDate = $this->faker->optional()->date();
        if ($rescheduledDate) {
            $rescheduledStartTime = $this->faker->dateTimeBetween($rescheduledDate . ' 08:00:00', $rescheduledDate . ' 10:00:00');
            $rescheduledEndTime = (clone $rescheduledStartTime)->modify('+1 hour');
            $rescheduleCount = $this->faker->numberBetween(1, 5);
            $rescheduleDuration = ($rescheduledEndTime->getTimestamp() - $rescheduledStartTime->getTimestamp()) / 60;
        } else {
            $rescheduledStartTime = null;
            $rescheduledEndTime = null;
            $rescheduleCount = 0;
            $rescheduleDuration = 0;
        }

        return [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $sessionDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'estimated_duration_minutes' => $duration,
            'rescheduled_date' => $rescheduledDate,
            'rescheduled_start_time' => $rescheduledStartTime,
            'rescheduled_end_time' => $rescheduledEndTime,
            'rescheduled_estimated_duration_minutes' => $rescheduleDuration,
            'reschedule_count' => $rescheduleCount,
            'topic' => $this->faker->optional()->sentence(),
            'activity' => $this->faker->optional()->sentence(),
            'order' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'canceled', 'rescheduled']),
        ];
    }
}
