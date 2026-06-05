<?php

namespace Tests\Feature\Api;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassRecord;
use App\Models\ClassRecordAttendance;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class MonthlyClassesReportReprogrammedSessionTest extends TestCase
{
    public function test_reprogrammed_session_has_is_reprogrammed_flag_and_rescheduled_label(): void
    {
        $this->app->setLocale('es');

        [$user, $course, $student, $teacher] = $this->createReportFixtures();

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-05-01',
        ]);

        $completedDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => '2026-05-10',
            'start_time' => Carbon::parse('2026-05-10 09:00:00'),
            'end_time' => Carbon::parse('2026-05-10 10:00:00'),
            'estimated_duration_minutes' => 60,
            'status' => ClassScheduleStatusEnum::COMPLETED->value,
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => '2026-05-17',
            'start_time' => Carbon::parse('2026-05-17 09:00:00'),
            'end_time' => Carbon::parse('2026-05-17 10:00:00'),
            'estimated_duration_minutes' => 60,
            'rescheduled_date' => '2026-05-24',
            'rescheduled_start_time' => Carbon::parse('2026-05-24 10:00:00'),
            'rescheduled_end_time' => Carbon::parse('2026-05-24 11:00:00'),
            'rescheduled_estimated_duration_minutes' => 60,
            'status' => ClassScheduleStatusEnum::REPROGRAMED->value,
        ]);

        $record = ClassRecord::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $completedDetail->id,
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => Carbon::parse('2026-05-10 09:00:00'),
            'end_time' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_minutes' => 60,
            'comments' => 'Session progress notes',
            'mode' => 'online',
        ]);

        ClassRecordAttendance::query()->create([
            'class_record_id' => $record->id,
            'student_id' => $student->id,
            'attendance' => 1.0,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.reports.monthly-classes'), [
                'course_id' => $course->id,
                'month' => '2026-05',
                'student_id' => $student->id,
            ]);

        $response->assertOk();

        $sessions = $response->json('data.reports.0.sessions');

        $completedSession = collect($sessions)->first(fn ($s) => $s['date'] === '2026-05-10');
        $this->assertNotNull($completedSession);
        $this->assertFalse($completedSession['is_reprogrammed']);
        $this->assertNull($completedSession['rescheduled_label']);
        $this->assertSame('Session progress notes', $completedSession['progress']);

        $reprogrammedSession = collect($sessions)->first(fn ($s) => $s['date'] === '2026-05-17');
        $this->assertNotNull($reprogrammedSession);
        $this->assertTrue($reprogrammedSession['is_reprogrammed']);
        $this->assertStringContainsString('24/05/2026', $reprogrammedSession['rescheduled_label']);
        $this->assertStringContainsString('10:00', $reprogrammedSession['rescheduled_label']);
        $this->assertStringContainsString('Reprogramada para el', $reprogrammedSession['rescheduled_label']);
    }

    public function test_reprogrammed_session_hours_are_excluded_from_totals(): void
    {
        [$user, $course, $student, $teacher] = $this->createReportFixtures();

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-05-01',
        ]);

        $completedDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => '2026-05-10',
            'start_time' => Carbon::parse('2026-05-10 09:00:00'),
            'end_time' => Carbon::parse('2026-05-10 10:00:00'),
            'estimated_duration_minutes' => 60,
            'status' => ClassScheduleStatusEnum::COMPLETED->value,
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => '2026-05-17',
            'start_time' => Carbon::parse('2026-05-17 09:00:00'),
            'end_time' => Carbon::parse('2026-05-17 10:00:00'),
            'estimated_duration_minutes' => 60,
            'rescheduled_date' => '2026-05-24',
            'rescheduled_start_time' => Carbon::parse('2026-05-24 10:00:00'),
            'rescheduled_end_time' => Carbon::parse('2026-05-24 11:00:00'),
            'rescheduled_estimated_duration_minutes' => 60,
            'status' => ClassScheduleStatusEnum::REPROGRAMED->value,
        ]);

        $record = ClassRecord::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $completedDetail->id,
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => Carbon::parse('2026-05-10 09:00:00'),
            'end_time' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_minutes' => 60,
            'comments' => '',
            'mode' => 'online',
        ]);

        ClassRecordAttendance::query()->create([
            'class_record_id' => $record->id,
            'student_id' => $student->id,
            'attendance' => 1.0,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.reports.monthly-classes'), [
                'course_id' => $course->id,
                'month' => '2026-05',
                'student_id' => $student->id,
            ]);

        $response->assertOk();

        $report = $response->json('data.reports.0');

        // Only the completed session (1 hour) should count; the reprogrammed one must be excluded
        $this->assertSame(1.0, (float) $report['total_hours_in_month']);
        $this->assertSame(1.0, (float) $report['totals']['hours']);
    }

    /**
     * @return array{0: User, 1: Course, 2: Student, 3: Teacher}
     */
    private function createReportFixtures(): array
    {
        $language = Language::factory()->create([
            'name' => 'Lang_'.uniqid(),
        ]);
        $level = LanguageLevel::factory()->create([
            'language_id' => $language->id,
            'level' => 'A1',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('admin');

        $course = Course::factory()->create([
            'language_id' => $language->id,
            'language_level_id' => $level->id,
            'status' => 'active',
        ]);

        $studentProfile = Profile::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Student',
        ]);
        $studentUser = User::factory()->create([
            'profile_id' => $studentProfile->id,
        ]);
        $student = Student::factory()->create([
            'profile_id' => $studentUser->profile_id,
            'status' => 'active',
        ]);
        $course->students()->attach($student->id);

        $teacherProfile = Profile::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Teacher',
        ]);
        $teacherUser = User::factory()->create([
            'profile_id' => $teacherProfile->id,
        ]);
        $teacher = Teacher::factory()->create([
            'profile_id' => $teacherUser->profile_id,
            'status' => 'active',
        ]);
        $course->teachers()->attach($teacher->id);

        return [$user, $course, $student, $teacher];
    }
}
