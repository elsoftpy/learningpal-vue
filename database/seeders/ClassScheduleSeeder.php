<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\ClassSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::query()
            ->with(['students', 'teachers'])
            ->whereHas('students')
            ->whereHas('teachers')
            ->orderBy('id')
            ->get();

        $firstLessonDate = CarbonImmutable::now()
            ->startOfWeek()
            ->subWeeks(10);

        foreach ($courses as $courseIndex => $course) {
            for ($week = 1; $week <= 10; $week++) {
                $sessionDate = $firstLessonDate
                    ->addWeeks($week - 1)
                    ->addDays($courseIndex % 2);

                ClassSchedule::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'name' => sprintf('%s - W%02d - %s', $course->name, $week, $sessionDate->toDateString()),
                    ],
                    [
                        'schedule_month' => $sessionDate->startOfMonth()->toDateString(),
                        'feedback' => null,
                    ]
                );
            }
        }
    }
}
