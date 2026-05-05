<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassRecord;
use App\Models\ClassRecordDetail;
use App\Models\ClassRecordDetailStudents;
use App\Models\ClassRecordStudent;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\ClassScheduleDetailStatusHistory;
use App\Models\Course;
use App\Models\Profile;
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

    public function test_teacher_can_delete_class_record_for_assigned_course(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $course = Course::factory()->create();
        $teacher->courses()->sync([$course->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertOk();
        $this->assertDatabaseMissing('class_records', ['id' => $classRecord->id]);
    }

    public function test_teacher_cannot_delete_class_record_for_unassigned_course(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $otherTeacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        // teacher is NOT assigned to $course

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        $classRecord = $this->makeClassRecord($course, $otherTeacher, $scheduleDetail, $user);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertForbidden();
        $this->assertDatabaseHas('class_records', ['id' => $classRecord->id]);
    }

    public function test_deleting_class_record_reverts_schedule_detail_status_to_previous(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => 'scheduled',
        ]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        ClassScheduleDetailStatusHistory::query()->create([
            'class_schedule_detail_id' => $scheduleDetail->id,
            'changed_by_user_id' => $user->id,
            'changed_by_type' => 'class_record',
            'old_status' => 'scheduled',
            'new_status' => 'completed',
            'action_type' => 'class_record_created',
            'created_at' => now(),
        ]);

        $scheduleDetail->update(['status' => 'completed']);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertOk();
        $this->assertDatabaseMissing('class_records', ['id' => $classRecord->id]);
        $this->assertDatabaseHas('class_schedule_details', [
            'id' => $scheduleDetail->id,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseMissing('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $scheduleDetail->id,
            'action_type' => 'class_record_created',
        ]);
    }

    public function test_deleting_class_record_without_history_does_not_change_schedule_detail_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $scheduleDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => 'completed',
        ]);

        $classRecord = $this->makeClassRecord($course, $teacher, $scheduleDetail, $user);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-records/{$classRecord->id}/destroy");

        $response->assertOk();
        $this->assertDatabaseMissing('class_records', ['id' => $classRecord->id]);
        $this->assertDatabaseHas('class_schedule_details', [
            'id' => $scheduleDetail->id,
            'status' => 'completed',
        ]);
    }
}
