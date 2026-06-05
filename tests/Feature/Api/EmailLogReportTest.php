<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\EmailLog;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class EmailLogReportTest extends TestCase
{
    public function test_admin_can_view_email_log_report(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $course = Course::factory()->create([
            'name' => 'Silvia Murdoch',
            'chat_room_link' => 'https://meet.google.com/tep-uwvu-uip',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-05-18 09:46:29'));

        EmailLog::recordNotification(
            emailDestino: 'teacheripl06@gmail.com',
            greeting: 'Ana Sanabria',
            hora: '08:00',
            estado: 'Enviado',
            url: 'https://meet.google.com/tep-uwvu-uip',
            context: [
                'course_id' => $course->id,
                'course_name' => 'Silvia Murdoch',
                'teacher_name' => 'Ana Sanabria',
                'student_name' => 'Silvia Murdoch',
                'action_type' => 'pending',
            ]
        );

        Carbon::setTestNow(Carbon::parse('2026-05-18 09:46:30'));

        EmailLog::recordNotification(
            emailDestino: 'hello@example.com',
            greeting: 'Ana Sanabria',
            hora: '08:00',
            estado: 'Error',
            url: null,
            error: 'SMTP failure',
            context: [
                'course_id' => $course->id,
                'course_name' => 'Silvia Murdoch',
                'teacher_name' => 'Ana Sanabria',
                'student_name' => 'Silvia Murdoch',
                'action_type' => 'pending',
            ]
        );

        Carbon::setTestNow();

        $response = $this->actingAs($admin, 'web')
            ->getJson(route('academics.reports.email-logs', [
                'search' => 'hello@example.com',
                'filters' => json_encode([
                    'estado' => 'Error',
                ]),
            ]));

        $response->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.logs.0.email_destino', 'hello@example.com')
            ->assertJsonPath('data.logs.0.estado', 'Error')
            ->assertJsonPath('data.logs.0.error', 'SMTP failure');
    }

    public function test_student_cannot_view_email_log_report(): void
    {
        /** @var User $student */
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student, 'web')
            ->getJson(route('academics.reports.email-logs'));

        $response->assertForbidden();
    }
}
