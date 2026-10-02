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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DistanceActivityStudentProductionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
    }

    public function test_student_can_upload_recorded_audio_and_then_complete_the_task(): void
    {
        [$studentUser, $detail] = $this->createProductionDetailForStudent();

        $this->actingAs($studentUser, 'web')
            ->post("/academics/lessons/distance-activities/details/{$detail->id}/student-production", [
                'student_production_audio' => UploadedFile::fake()->create('student-production.webm', 2400, 'audio/webm'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $studentDetail = DistanceActivityDetailStudent::query()
            ->where('distance_activity_detail_id', $detail->id)
            ->where('student_id', $studentUser->profile->student->id)
            ->firstOrFail();

        $media = $studentDetail->getMedia('student-production');
        $this->assertCount(1, $media);
        $this->assertSame('audio', $media->first()->getCustomProperty('media_type'));

        $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/complete", [
                'completed' => true,
            ])
            ->assertOk();
    }

    public function test_audio_larger_than_ten_megabytes_is_rejected_with_a_field_error(): void
    {
        [$studentUser, $detail] = $this->createProductionDetailForStudent();

        $this->actingAs($studentUser, 'web')
            ->post("/academics/lessons/distance-activities/details/{$detail->id}/student-production", [
                'student_production_audio' => UploadedFile::fake()->create('student-production.webm', 10241, 'audio/webm'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['student_production_audio']);
    }

    public function test_saving_without_any_file_is_rejected(): void
    {
        [$studentUser, $detail] = $this->createProductionDetailForStudent();

        $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/student-production", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['student_production']);
    }

    public function test_task_cannot_be_completed_before_the_production_is_saved(): void
    {
        [$studentUser, $detail] = $this->createProductionDetailForStudent();

        $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$detail->id}/complete", [
                'completed' => true,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.completed.0', __('You must save your production before marking this task as completed.'));
    }

    public function test_production_task_is_not_locked_behind_unopened_previous_videos(): void
    {
        [$studentUser, $productionDetail, $videoDetail, $laterExerciseDetail] = $this->createVideoThenProductionDetailsForStudent();

        $details = collect(
            $this->actingAs($studentUser, 'web')
                ->postJson("/academics/lessons/distance-activities/{$productionDetail->distance_activity_id}/data")
                ->assertOk()
                ->json('data.distance_activity.details')
        )->keyBy('id');

        $this->assertSame(1, $details[$videoDetail->id]['sequence']);
        $this->assertNull($details[$productionDetail->id]['completion_lock_message']);
        $this->assertNull($details[$productionDetail->id]['next_completion_locked_until']);
        $this->assertSame(
            __('Open the previous video activity first.'),
            $details[$laterExerciseDetail->id]['completion_lock_message']
        );
    }

    public function test_student_can_upload_and_complete_production_before_opening_previous_videos(): void
    {
        [$studentUser, $productionDetail] = $this->createVideoThenProductionDetailsForStudent();

        $this->actingAs($studentUser, 'web')
            ->post("/academics/lessons/distance-activities/details/{$productionDetail->id}/student-production", [
                'student_production_audio' => UploadedFile::fake()->create('student-production.webm', 500, 'audio/webm'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->actingAs($studentUser, 'web')
            ->postJson("/academics/lessons/distance-activities/details/{$productionDetail->id}/complete", [
                'completed' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('distance_activity_detail_students', [
            'distance_activity_detail_id' => $productionDetail->id,
            'student_id' => $studentUser->profile->student->id,
            'completed' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: DistanceActivityDetail, 2: DistanceActivityDetail, 3: DistanceActivityDetail}
     */
    private function createVideoThenProductionDetailsForStudent(): array
    {
        [$studentUser, $existingDetail] = $this->createProductionDetailForStudent();
        $distanceActivityId = $existingDetail->distance_activity_id;
        $existingDetail->delete();

        [$videoDetail, $productionDetail, $laterExerciseDetail] = collect([
            StudyProgramActivityTypeEnum::VIDEO,
            StudyProgramActivityTypeEnum::PRODUCTION,
            StudyProgramActivityTypeEnum::EXERCISE,
        ])->map(fn (StudyProgramActivityTypeEnum $type) => DistanceActivityDetail::factory()->create([
            'distance_activity_id' => $distanceActivityId,
            'study_program_week_activity_id' => null,
            'type' => $type->value,
            'links' => null,
        ]))->all();

        return [$studentUser, $productionDetail, $videoDetail, $laterExerciseDetail];
    }

    /**
     * @return array{0: User, 1: DistanceActivityDetail}
     */
    private function createProductionDetailForStudent(): array
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create([
            'profile_id' => $profile->id,
        ]);

        $studentUser = User::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $studentUser->assignRole('student');

        $activity = DistanceActivity::factory()->create([
            'user_id' => User::factory()->create()->id,
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
            'type' => StudyProgramActivityTypeEnum::PRODUCTION->value,
            'links' => null,
        ]);

        return [$studentUser, $detail];
    }
}
