<?php

namespace Tests\Feature\Api\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class ClassRecordStoreTest extends TestCase
{
    public function test_it_stores_class_record_with_long_links_in_details(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $student = Student::factory()->create();
        $course->students()->attach($student->id);

        $teacher = Teacher::factory()->create();

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
        ]);

        $scheduleDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $longUrl = 'https://www.bing.com/ck/a?!&&p=3c195bec44ee980bJmltdHM9MTcyNjk2MzIwMCZpZ3VpZD0yYzYyODViZS1lYWVkLTY4OTktMmUxZC05MTk5ZWI2NzY5NjgmaW5zaWQ9NTIwMg&ptn=3&ver=2&hsh=3&fclid=2c6285be-eaed-6899-2e1d-9199eb676968&psq=Factual+conditionals%3a+Present&u=a1aHR0cHM6Ly93d3cudXNpbmdlbmdsaXNoLmNvbS9hcnRpY2xlcy9jb25kaXRpb25hbC1zZW50ZW5jZXMtaW4tZW5nbGlzaC0yLmh0bWw&ntb=1';

        $payload = [
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $scheduleDetail->id,
            'date' => now()->format('d/m/Y'),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'comments' => 'Test class record',
            'student_attendances' => [
                [
                    'student_id' => $student->id,
                    'attendance' => '1.0',
                ],
            ],
            'details' => [
                [
                    'free_content' => 'Content 1.1',
                    'activity' => 'Factual conditionals: Present',
                    'links' => $longUrl,
                ],
            ],
        ];

        $response = $this->actingAs($user, 'web')
            ->postJson('/academics/lessons/class-records', $payload);

        $response->assertOk();

        $classRecord = ClassRecord::query()->latest('id')->first();
        $this->assertNotNull($classRecord, 'Class record should be created');

        $this->assertDatabaseHas('class_record_details', [
            'class_record_id' => $classRecord->id,
            'activity' => 'Factual conditionals: Present',
            'links' => $longUrl,
        ]);
    }
}
