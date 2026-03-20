<?php

namespace Database\Seeders;

use App\Models\ClassSchedule;
use App\Models\Course;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if ($courses->isEmpty()) {
            return;
        }

        // Class schedules/records are derived data. Recreate them from scratch on each seed run.
        Schema::disableForeignKeyConstraints();
        DB::table('class_record_detail_students')->truncate();
        DB::table('class_record_details')->truncate();
        DB::table('class_record_students')->truncate();
        DB::table('class_records')->truncate();
        DB::table('class_schedule_details')->truncate();
        DB::table('class_schedules')->truncate();
        Schema::enableForeignKeyConstraints();

        $firstMonth = CarbonImmutable::now()
            ->startOfMonth()
            ->subMonths(2);

        foreach ($courses as $course) {
            for ($monthOffset = 0; $monthOffset < 3; $monthOffset++) {
                $monthDate = $firstMonth->addMonths($monthOffset);

                ClassSchedule::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'schedule_month' => $monthDate->toDateString(),
                    ],
                    [
                        'name' => sprintf('%s - %s', $course->name, $monthDate->format('F Y')),
                        'feedback' => null,
                    ]
                );
            }
        }
    }
}
