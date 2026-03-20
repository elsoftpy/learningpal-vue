<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\User;
use Tests\TestCase;

class ClassScheduleSpaTest extends TestCase
{
    public function test_authenticated_admin_sees_newer_schedule_months_first_by_default(): void
    {
        $search = 'Default Sort Schedule';
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' January',
            'schedule_month' => '2026-01-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' March',
            'schedule_month' => '2026-03-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' February',
            'schedule_month' => '2026-02-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/academics/lessons/class-schedules?search='.urlencode($search).'&per_page=10');

        $response->assertOk();

        $this->assertSame(
            [$search.' March', $search.' February', $search.' January'],
            array_column($response->json('data.class_schedules'), 'name')
        );
    }

    public function test_authenticated_admin_can_sort_class_schedules_by_id_in_ascending_order(): void
    {
        $search = 'ID Sort Schedule';
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();

        $third = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' Third',
            'schedule_month' => '2026-03-01',
        ]);

        $first = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' First',
            'schedule_month' => '2026-01-01',
        ]);

        $second = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' Second',
            'schedule_month' => '2026-02-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/academics/lessons/class-schedules?search='.urlencode($search).'&sort_field=id&sort_order=asc&per_page=10');

        $response->assertOk();

        $this->assertSame(
            [$third->id, $first->id, $second->id],
            array_column($response->json('data.class_schedules'), 'id')
        );
    }

    public function test_authenticated_admin_can_sort_class_schedules_by_name_in_descending_order(): void
    {
        $search = 'Name Sort Schedule';
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' Alpha',
            'schedule_month' => '2026-01-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' Charlie',
            'schedule_month' => '2026-03-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' Bravo',
            'schedule_month' => '2026-02-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/academics/lessons/class-schedules?search='.urlencode($search).'&sort_field=name&sort_order=desc&per_page=10');

        $response->assertOk();

        $this->assertSame(
            [$search.' Charlie', $search.' Bravo', $search.' Alpha'],
            array_column($response->json('data.class_schedules'), 'name')
        );
    }

    public function test_authenticated_admin_can_sort_class_schedules_by_schedule_month_in_ascending_order(): void
    {
        $search = 'Month Sort Schedule';
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' March',
            'schedule_month' => '2026-03-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' January',
            'schedule_month' => '2026-01-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'name' => $search.' February',
            'schedule_month' => '2026-02-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/academics/lessons/class-schedules?search='.urlencode($search).'&sort_field=schedule_month&sort_order=asc&per_page=10');

        $response->assertOk();

        $this->assertSame(
            [$search.' January', $search.' February', $search.' March'],
            array_column($response->json('data.class_schedules'), 'name')
        );
    }
}