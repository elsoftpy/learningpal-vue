<?php

namespace Tests\Feature;

use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackfillLanguageLevelIdTest extends TestCase
{
    use RefreshDatabase;

    private function runMigrationUp(): void
    {
        $migration = require database_path('migrations/2026_05_06_230912_backfill_language_level_id_on_class_records_and_distance_activities.php');
        $migration->up();
    }

    private function runMigrationDown(): void
    {
        $migration = require database_path('migrations/2026_05_06_230912_backfill_language_level_id_on_class_records_and_distance_activities.php');
        $migration->down();
    }

    public function test_backfill_sets_language_level_id_on_class_records(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $record = ClassRecord::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $detail->id,
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'mode' => 'online',
        ]);

        // Nullify to simulate pre-backfill state
        DB::table('class_records')->where('id', $record->id)->update(['language_level_id' => null]);

        $this->assertDatabaseHas('class_records', ['id' => $record->id, 'language_level_id' => null]);

        $this->runMigrationUp();

        $this->assertDatabaseHas('class_records', [
            'id' => $record->id,
            'language_level_id' => $course->language_level_id,
        ]);
    }

    public function test_backfill_skips_class_records_already_having_language_level_id(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $record = ClassRecord::query()->create([
            'course_id' => $course->id,
            'language_level_id' => $course->language_level_id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $detail->id,
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'mode' => 'online',
        ]);

        $this->runMigrationUp();

        $this->assertDatabaseHas('class_records', [
            'id' => $record->id,
            'language_level_id' => $course->language_level_id,
        ]);
    }

    public function test_backfill_sets_language_level_id_on_distance_activities(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();

        $activity = DistanceActivity::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'user_id' => $user->id,
            'title' => 'Week 1',
            'comments' => null,
        ]);

        // Nullify to simulate pre-backfill state
        DB::table('distance_activities')->where('id', $activity->id)->update(['language_level_id' => null]);

        $this->assertDatabaseHas('distance_activities', ['id' => $activity->id, 'language_level_id' => null]);

        $this->runMigrationUp();

        $this->assertDatabaseHas('distance_activities', [
            'id' => $activity->id,
            'language_level_id' => $course->language_level_id,
        ]);
    }

    public function test_backfill_skips_distance_activities_already_having_language_level_id(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();

        $activity = DistanceActivity::query()->create([
            'course_id' => $course->id,
            'language_level_id' => $course->language_level_id,
            'teacher_id' => $teacher->id,
            'user_id' => $user->id,
            'title' => 'Week 1',
            'comments' => null,
        ]);

        $this->runMigrationUp();

        $this->assertDatabaseHas('distance_activities', [
            'id' => $activity->id,
            'language_level_id' => $course->language_level_id,
        ]);
    }

    public function test_down_nullifies_language_level_id_on_both_tables(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        ClassRecord::query()->create([
            'course_id' => $course->id,
            'language_level_id' => $course->language_level_id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $detail->id,
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'mode' => 'online',
        ]);

        DistanceActivity::query()->create([
            'course_id' => $course->id,
            'language_level_id' => $course->language_level_id,
            'teacher_id' => $teacher->id,
            'user_id' => $user->id,
            'title' => 'Week 1',
            'comments' => null,
        ]);

        $this->runMigrationDown();

        $this->assertDatabaseMissing('class_records', ['language_level_id' => $course->language_level_id]);
        $this->assertDatabaseMissing('distance_activities', ['language_level_id' => $course->language_level_id]);
    }
}
