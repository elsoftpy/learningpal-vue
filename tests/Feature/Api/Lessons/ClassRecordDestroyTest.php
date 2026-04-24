<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassRecord;
use App\Models\ClassRecordDetail;
use App\Models\ClassRecordDetailStudents;
use App\Models\ClassRecordStudent;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class ClassRecordDestroyTest extends TestCase
{
    private function makeClassRecord(Course $course, Teacher $teacher, ClassScheduleDetail $detail, User $user): ClassRecord
    {
        return ClassRecord::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $detail->id,
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'comments' => null,
            'mode' => 'online',
        ]);
    }

    public function test_deleting_class_record_also_removes_child_records(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        $recordDetail = ClassRecordDetail::query()->create([
            'class_record_id' => $classRecord->id,
            'content_id' => null,
            'free_content' => 'Test content',
            'activity' => 'some activity',
            'links' => null,
            'file_path' => null,
            'file_name' => null,
        ]);

        ClassRecordDetailStudents::query()->create([
            'class_record_detail_id' => $recordDetail->id,
            'student_id' => $student->id,
        ]);

        ClassRecordStudent::query()->create([
            'class_record_id' => $classRecord->id,
            'student_id' => $student->id,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertOk();
        $this->assertDatabaseMissing('class_records', ['id' => $classRecord->id]);
        $this->assertDatabaseMissing('class_record_details', ['id' => $recordDetail->id]);
        $this->assertDatabaseMissing('class_record_detail_students', ['class_record_detail_id' => $recordDetail->id]);
        $this->assertDatabaseMissing('class_record_students', ['class_record_id' => $classRecord->id]);
    }

    public function test_deleting_class_record_without_children_succeeds(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertOk();
        $this->assertDatabaseMissing('class_records', ['id' => $classRecord->id]);
    }

    public function test_deleting_class_record_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertForbidden();
        $this->assertDatabaseHas('class_records', ['id' => $classRecord->id]);
    }
}
