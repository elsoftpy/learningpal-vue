<?php

namespace Tests\Feature;

use App\Enums\StudyProgramActivityTypeEnum;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityDetail;
use App\Models\DistanceActivityDetailStudent;
use App\Models\DistanceActivityStudent;
use App\Models\Profile;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DistanceActivityLinkTimerTest extends TestCase
{
    public function test_student_can_start_timer_for_exercise_link(): void
    {
        [$studentUser, $detail] = $this->createAssignedDetailForStudent(
            StudyProgramActivityTypeEnum::EXERCISE,
            "https://example.com/a\nhttps://example.com/b"
        );

        $response = $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/video-open", [
                'link_index' => 0,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $studentDetail = DistanceActivityDetailStudent::query()
            ->where('distance_activity_detail_id', $detail->id)
            ->where('student_id', $studentUser->profile->student->id)
            ->first();

        $this->assertNotNull($studentDetail);
        $this->assertIsArray($studentDetail->link_opened_at_map);
        $this->assertArrayHasKey('0', $studentDetail->link_opened_at_map);
    }

    public function test_student_cannot_start_second_timer_while_first_timer_is_active(): void
    {
        [$studentUser, $firstDetail, $activity] = $this->createAssignedDetailForStudentWithActivity(
            StudyProgramActivityTypeEnum::VIDEO,
            'https://example.com/video'
        );

        $secondDetail = DistanceActivityDetail::factory()->create([
            'distance_activity_id' => $activity->id,
            'type' => StudyProgramActivityTypeEnum::EXERCISE->value,
            'links' => 'https://example.com/exercise',
        ]);

        $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$firstDetail->id}/video-open", [
                'link_index' => 0,
            ])
            ->assertOk();

        $response = $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$secondDetail->id}/video-open", [
                'link_index' => 0,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('errors.link.0', __('You already have an active timer. Please wait until it finishes before opening another link.'));
    }

    public function test_student_must_open_all_exercise_links_and_wait_one_minute_before_completion(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 12:00:00'));

        try {
            [$studentUser, $detail] = $this->createAssignedDetailForStudent(
                StudyProgramActivityTypeEnum::EXERCISE,
                "https://example.com/a\nhttps://example.com/b"
            );

            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/video-open", [
                    'link_index' => 0,
                ])
                ->assertOk();

            Carbon::setTestNow(Carbon::parse('2026-05-15 12:01:05'));

            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/complete", [
                    'completed' => true,
                ])
                ->assertStatus(422)
                ->assertJsonPath('errors.completed.0', __('You must open every link before marking this task as completed.'));

            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/video-open", [
                    'link_index' => 1,
                ])
                ->assertOk();

            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/complete", [
                    'completed' => true,
                ])
                ->assertStatus(422)
                ->assertJsonPath('errors.completed.0', __('You must wait :minutes minutes after opening each link before marking this task as completed.', [
                    'minutes' => config('academics.distance_activities.video_completion_lock_minutes', 1),
                ]));

            Carbon::setTestNow(Carbon::parse('2026-05-15 12:02:10'));

            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/complete", [
                    'completed' => true,
                ])
                ->assertOk();

            $studentDetail = DistanceActivityDetailStudent::query()
                ->where('distance_activity_detail_id', $detail->id)
                ->where('student_id', $studentUser->profile->student->id)
                ->first();

            $this->assertNotNull($studentDetail);
            $this->assertTrue((bool) $studentDetail->completed);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createAssignedDetailForStudent(StudyProgramActivityTypeEnum $type, string $links): array
    {
        [$studentUser, $detail] = $this->createAssignedDetailForStudentWithActivity($type, $links);

        return [$studentUser, $detail];
    }

    private function createAssignedDetailForStudentWithActivity(StudyProgramActivityTypeEnum $type, string $links): array
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create([
            'profile_id' => $profile->id,
        ]);

        $studentUser = User::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $studentUser->assignRole('student');

        $owner = User::factory()->create();
        $activity = DistanceActivity::factory()->create([
            'user_id' => $owner->id,
        ]);

        $activity->course->students()->syncWithoutDetaching([$student->id]);

        DistanceActivityStudent::query()->create([
            'distance_activity_id' => $activity->id,
            'student_id' => $student->id,
            'completed' => false,
            'completed_at' => null,
        ]);

        $detail = DistanceActivityDetail::factory()->create([
            'distance_activity_id' => $activity->id,
            'type' => $type->value,
            'links' => $links,
        ]);

        return [$studentUser, $detail, $activity];
    }
}
