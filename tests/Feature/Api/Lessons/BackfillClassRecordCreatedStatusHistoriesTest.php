<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\ClassScheduleDetailStatusHistory;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillClassRecordCreatedStatusHistoriesTest extends TestCase
{
    use RefreshDatabase;

    private function runMigrationUp(): void
    {
        $migration = require database_path('migrations/2026_05_05_115733_backfill_class_record_created_status_histories.php');
        $migration->up();
    }

    public function test_backfill_inserts_history_for_completed_details_without_one(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => 'completed',
        ]);
        ClassRecord::query()->create([
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

        $this->assertDatabaseMissing('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $detail->id,
            'action_type' => 'class_record_created',
        ]);

        $this->runMigrationUp();

        $this->assertDatabaseHas('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $detail->id,
            'action_type' => 'class_record_created',
            'old_status' => 'scheduled',
            'new_status' => 'completed',
            'changed_by_type' => 'class_record',
            'changed_by_user_id' => $user->id,
        ]);
    }

    public function test_backfill_skips_details_that_already_have_a_class_record_created_entry(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => 'completed',
        ]);
        ClassScheduleDetailStatusHistory::query()->create([
            'class_schedule_detail_id' => $detail->id,
            'changed_by_user_id' => $user->id,
            'changed_by_type' => 'class_record',
            'old_status' => 'scheduled',
            'new_status' => 'completed',
            'action_type' => 'class_record_created',
            'created_at' => now(),
        ]);

        $this->runMigrationUp();

        $this->assertSame(
            1,
            ClassScheduleDetailStatusHistory::query()
                ->where('class_schedule_detail_id', $detail->id)
                ->where('action_type', 'class_record_created')
                ->count()
        );
    }

    public function test_backfill_skips_non_completed_details(): void
    {
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => 'scheduled',
        ]);

        $this->runMigrationUp();

        $this->assertDatabaseMissing('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $detail->id,
            'action_type' => 'class_record_created',
        ]);
    }
}
