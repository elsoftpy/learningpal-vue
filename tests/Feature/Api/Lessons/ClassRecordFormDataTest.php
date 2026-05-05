<?php

namespace Tests\Feature\Api\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\User;
use Tests\TestCase;

class ClassRecordFormDataTest extends TestCase
{
    public function test_form_data_includes_canceled_schedule_details(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);

        $canceledDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::CANCELED->value,
        ]);

        $scheduledDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.lessons.class-records.form-data'));

        $response->assertOk();

        $ids = collect($response->json('data.class_schedule_details'))->pluck('id');

        $this->assertTrue($ids->contains($canceledDetail->id), 'Canceled detail should be included');
        $this->assertTrue($ids->contains($scheduledDetail->id), 'Scheduled detail should be included');
    }

    public function test_form_data_excludes_completed_schedule_details(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);

        $completedDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::COMPLETED->value,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.lessons.class-records.form-data'));

        $response->assertOk();

        $ids = collect($response->json('data.class_schedule_details'))->pluck('id');

        $this->assertFalse($ids->contains($completedDetail->id), 'Completed detail should not be included');
    }
}
