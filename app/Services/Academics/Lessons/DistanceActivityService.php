<?php

namespace App\Services\Academics\Lessons;

use App\Models\DistanceActivity;
use App\Models\DistanceActivityDetail;
use App\Models\DistanceActivityDetailStudent;
use App\Models\DistanceActivityStudent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DistanceActivityService
{
    public function canViewAny(User $user): bool
    {
        return $user->can('view all distance activities')
            || $user->can('view own distance activities')
            || $user->can('view assigned distance activities');
    }

    public function visibleActivitiesQuery(User $user): Builder
    {
        $query = DistanceActivity::query()
            ->with([
                'course',
                'teacher.profile',
                'user.profile',
                'students.student.profile.user',
                'details.studyProgramWeekActivity.media',
                'details.content',
                'details.students.student.profile.user',
                'details.students.media',
            ]);

        $student = $this->resolveStudent($user);

        if ($user->can('view all distance activities')) {
            return $query;
        }

        $query->where(function (Builder $visibleQuery) use ($student, $user) {
            $hasCondition = false;

            if ($user->can('view assigned distance activities')) {
                if ($student) {
                    $visibleQuery->whereHas('students', function (Builder $studentQuery) use ($student) {
                        $studentQuery->where('student_id', $student->id);
                    });
                    $hasCondition = true;
                }
            }

            if ($user->can('view own distance activities')) {
                if ($hasCondition) {
                    $visibleQuery->orWhere(function (Builder $ownQuery) use ($user) {
                        $ownQuery
                            ->where('user_id', $user->id)
                            ->orWhereHas('teacher.profile.user', function (Builder $teacherUserQuery) use ($user) {
                                $teacherUserQuery->where('id', $user->id);
                            });
                    });
                } else {
                    $visibleQuery
                        ->where('user_id', $user->id)
                        ->orWhereHas('teacher.profile.user', function (Builder $teacherUserQuery) use ($user) {
                            $teacherUserQuery->where('id', $user->id);
                        });
                }
            }
        });

        if (
            !$user->can('view all distance activities')
            && !$user->can('view own distance activities')
            && $user->can('view assigned distance activities')
            && !$student
        ) {
            $query->whereRaw('1 = 0');
        }


        return $query;
    }

    public function listData(DistanceActivity $activity, User $user): array
    {
        $student = $this->resolveStudent($user);
        $studentActivity = $student
            ? $activity->students->firstWhere('student_id', $student->id)
            : null;
        $totalAssigned = $activity->students->count();
        $completedAssigned = $activity->students->where('completed', true)->count();
        $totalTasks = $activity->details->count();
        $completedTasks = $activity->details
            ->filter(fn (DistanceActivityDetail $detail) => $student && $detail->students->firstWhere('student_id', $student->id)?->completed)
            ->count();
        $viewerMode = $this->viewerMode($activity, $user, $student);
        $isStudentView = $viewerMode === 'student';
        $managerCompleted = $totalAssigned > 0 && $completedAssigned === $totalAssigned;

        return [
            'id' => $activity->id,
            'teacher_name' => $activity->teacher?->profile?->full_name
                ?? $activity->user?->profile?->full_name
                ?? $activity->user?->name
                ?? '',
            'course_name' => $activity->course?->name ?? '',
            'title' => $activity->title,
            'comments' => $activity->comments,
            'completed' => $isStudentView ? (bool) $studentActivity?->completed : $managerCompleted,
            'completed_at' => $isStudentView ? $studentActivity?->completed_at : null,
            'status' => $isStudentView
                ? ($studentActivity?->completed ? 'completed' : 'pending')
                : ($managerCompleted ? 'completed' : 'pending'),
            'display_status' => $isStudentView
                ? ($studentActivity?->completed ? __('Completed') : __('Pending'))
                : ($managerCompleted ? __('Completed') : __('Pending')),
            'progress' => $isStudentView
                ? sprintf('%d/%d', $completedTasks, $totalTasks)
                : sprintf('%d/%d', $completedAssigned, $totalAssigned),
            'progress_completed' => $isStudentView ? $completedTasks : $completedAssigned,
            'progress_total' => $isStudentView ? $totalTasks : $totalAssigned,
            'viewer_mode' => $viewerMode,
        ];
    }

    public function detailData(DistanceActivity $activity, User $user): array
    {
        $activity->loadMissing([
            'course',
            'teacher.profile',
            'user.profile',
            'students.student.profile.user',
            'details.studyProgramWeekActivity.media',
            'details.content',
            'details.students.student.profile.user',
            'details.students.media',
        ]);

        $student = $this->resolveStudent($user);
        $studentActivity = $student
            ? $activity->students->firstWhere('student_id', $student->id)
            : null;
        $orderedDetails = $this->orderedDetails($activity);
        $viewerMode = $this->viewerMode($activity, $user, $student);
        $isStudentView = $viewerMode === 'student';

        return [
            ...$this->listData($activity, $user),
            'student_assignments' => $this->activityStudentAssignments($activity),
            'details' => $orderedDetails->map(function (DistanceActivityDetail $detail, int $index) use ($activity, $isStudentView, $student, $user, $orderedDetails) {
                $studentDetail = $student
                    ? $detail->students->firstWhere('student_id', $student->id)
                    : null;
                $studyMaterial = $detail->studyProgramWeekActivity?->getFirstMedia('study-materials');
                $detailStudents = $detail->students;
                $completedStudentsCount = $detailStudents->where('completed', true)->count();
                $assignedStudentsCount = $activity->students->count();

                return [
                    'id' => $detail->id,
                    'distance_activity_id' => $detail->distance_activity_id,
                    'student_detail_id' => $studentDetail?->id,
                    'sequence' => $index + 1,
                    'content_id' => $detail->content_id,
                    'content_name' => $detail->content?->content ?? $detail->free_content,
                    'free_content' => $detail->free_content,
                    'activity' => $detail->activity,
                    'type' => $detail->type->value,
                    'display_type' => $detail->type->label($detail->type->value),
                    'links' => $detail->links,
                    'completed' => $isStudentView
                        ? (bool) $studentDetail?->completed
                        : ($assignedStudentsCount > 0 && $completedStudentsCount === $assignedStudentsCount),
                    'completed_at' => $isStudentView ? $studentDetail?->completed_at : null,
                    'video_opened_at' => $isStudentView ? $studentDetail?->video_opened_at : null,
                    'study_material_url' => $studyMaterial?->getUrl(),
                    'study_material_name' => $studyMaterial?->file_name,
                    'student_production_media' => $studentDetail
                        ? $this->studentProductionMedia($studentDetail)
                        : [],
                    'next_completion_locked_until' => $isStudentView ? $this->nextCompletionLockedUntil($detail, $orderedDetails, $user) : null,
                    'completion_lock_message' => $isStudentView ? $this->completionLockMessage($detail, $orderedDetails, $user) : null,
                    'completed_students_count' => $completedStudentsCount,
                    'assigned_students_count' => $assignedStudentsCount,
                    'student_submissions' => $this->detailStudentSubmissions($detail),
                ];
            })->values()->all(),
            'student_assignment' => $studentActivity ? [
                'id' => $studentActivity->id,
                'completed' => $studentActivity->completed,
                'completed_at' => $studentActivity->completed_at,
            ] : null,
        ];
    }

    public function findAccessibleActivity(DistanceActivity $activity, User $user): ?DistanceActivity
    {
        if (!$this->canViewAny($user)) {
            return null;
        }

        return $this->visibleActivitiesQuery($user)
            ->whereKey($activity->id)
            ->first();
    }

    public function markDetailCompletion(DistanceActivityDetail $detail, User $user, bool $completed = true): ?DistanceActivity
    {
        if (!$completed && !$user->can('reset distance activity completion')) {
            return null;
        }

        if ($completed && !$user->can('complete own distance activity tasks') && !$user->can('reset distance activity completion')) {
            return null;
        }

        $activity = $this->findAccessibleActivity($detail->distanceActivity, $user);
        if (!$activity) {
            return null;
        }

        $studentDetail = $this->resolveOwnStudentDetail($detail, $user);
        if (!$studentDetail) {
            return null;
        }

        if ($completed) {
            $completionRequirementError = $this->validateDetailCompletionRequirements($detail, $studentDetail);
            if ($completionRequirementError) {
                throw new \RuntimeException($completionRequirementError);
            }

            $lockError = $this->validatePreviousVideoGate($detail, $studentDetail->student_id);
            if ($lockError) {
                throw new \RuntimeException($lockError);
            }
        }

        $studentDetail->update([
            'completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        $this->syncDistanceActivityStudentCompletion($detail->distanceActivity, $studentDetail->student_id);

        return $this->findAccessibleActivity($detail->distanceActivity, $user);
    }

    public function recordVideoOpen(DistanceActivityDetail $detail, User $user): ?DistanceActivityDetailStudent
    {
        if (!$user->can('complete own distance activity tasks')) {
            return null;
        }

        if ($detail->type->value !== 'video') {
            return null;
        }

        $activity = $this->findAccessibleActivity($detail->distanceActivity, $user);
        if (!$activity) {
            return null;
        }

        $studentDetail = $this->resolveOwnStudentDetail($detail, $user);
        if (!$studentDetail) {
            return null;
        }

        if (!$studentDetail->video_opened_at) {
            $studentDetail->update([
                'video_opened_at' => now(),
            ]);
        }

        return $studentDetail->fresh();
    }

    public function saveStudentProduction(
        DistanceActivityDetail $detail,
        User $user,
        array $files
    ): ?DistanceActivityDetailStudent {
        if (!$user->can('upload own distance activity production')) {
            return null;
        }

        if ($detail->type->value !== 'production') {
            return null;
        }

        $activity = $this->findAccessibleActivity($detail->distanceActivity, $user);
        if (!$activity) {
            return null;
        }

        $studentDetail = $this->resolveOwnStudentDetail($detail, $user);
        if (!$studentDetail) {
            return null;
        }

        $mediaInputs = [
            'student_production_file' => 'file',
            'student_production_audio' => 'audio',
        ];

        foreach ($mediaInputs as $inputKey => $mediaType) {
            $uploadedFile = $files[$inputKey] ?? null;
            if (!$uploadedFile instanceof UploadedFile) {
                continue;
            }

            $studentDetail->getMedia('student-production')
                ->filter(fn ($media) => $media->getCustomProperty('media_type') === $mediaType)
                ->each(fn ($media) => $media->delete());

            $studentDetail
                ->addMedia($uploadedFile)
                ->withCustomProperties(['media_type' => $mediaType])
                ->toMediaCollection('student-production');
        }

        return $studentDetail->fresh('media');
    }

    public function updateManagedDetailCompletion(
        DistanceActivityDetailStudent $studentDetail,
        User $user,
        bool $completed
    ): ?DistanceActivity {
        if (!$user->can('reset distance activity completion')) {
            return null;
        }

        $studentDetail->loadMissing('distanceActivityDetail.distanceActivity');
        $detail = $studentDetail->distanceActivityDetail;
        $activity = $detail?->distanceActivity;

        if (!$detail || !$activity) {
            return null;
        }

        $accessibleActivity = $this->findAccessibleActivity($activity, $user);
        if (!$accessibleActivity) {
            return null;
        }

        $studentDetail->update([
            'completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        $this->syncDistanceActivityStudentCompletion($activity, $studentDetail->student_id);

        return $this->findAccessibleActivity($activity, $user);
    }

    public function deleteStudentSubmissionMedia(
        DistanceActivityDetailStudent $studentDetail,
        Media $media,
        User $user
    ): ?DistanceActivity {
        if (!$user->can('delete distance activity submissions')) {
            return null;
        }

        $studentDetail->loadMissing('distanceActivityDetail.distanceActivity');
        $detail = $studentDetail->distanceActivityDetail;
        $activity = $detail?->distanceActivity;

        if (
            !$detail
            || !$activity
            || $media->model_type !== DistanceActivityDetailStudent::class
            || (int) $media->model_id !== (int) $studentDetail->id
        ) {
            return null;
        }

        $accessibleActivity = $this->findAccessibleActivity($activity, $user);
        if (!$accessibleActivity) {
            return null;
        }

        $media->delete();

        return $this->findAccessibleActivity($activity, $user);
    }

    protected function resolveOwnStudentDetail(DistanceActivityDetail $detail, User $user): ?DistanceActivityDetailStudent
    {
        $student = $this->resolveStudent($user);
        if (! $student) {
            return null;
        }

        $detail->loadMissing('students', 'distanceActivity.students');

        $studentAssignment = $detail->distanceActivity->students->firstWhere('student_id', $student->id)
            ?? DistanceActivityStudent::query()->firstOrCreate(
                [
                    'distance_activity_id' => $detail->distance_activity_id,
                    'student_id' => $student->id,
                ],
                [
                    'completed' => false,
                    'completed_at' => null,
                ]
            );

        if (!$studentAssignment) {
            return null;
        }

        return DistanceActivityDetailStudent::query()->firstOrCreate(
            [
                'distance_activity_detail_id' => $detail->id,
                'student_id' => $student->id,
            ],
            [
                'completed' => false,
                'completed_at' => null,
                'video_opened_at' => null,
            ]
        );
    }

    protected function orderedDetails(DistanceActivity $activity)
    {
        return $activity->details
            ->sortBy(function (DistanceActivityDetail $detail) {
                return $detail->studyProgramWeekActivity?->sort_order ?? $detail->id;
            })
            ->values();
    }

    protected function validatePreviousVideoGate(DistanceActivityDetail $detail, int $studentId): ?string
    {
        $orderedDetails = $this->orderedDetails($detail->distanceActivity);
        $currentIndex = $orderedDetails->search(fn (DistanceActivityDetail $orderedDetail) => $orderedDetail->id === $detail->id);

        if (!is_int($currentIndex) || $currentIndex <= 0) {
            return null;
        }

        foreach ($orderedDetails->slice(0, $currentIndex) as $previousDetail) {
            if ($previousDetail->type->value !== 'video') {
                continue;
            }

            $previousStudentDetail = $previousDetail->students->firstWhere('student_id', $studentId)
                ?? DistanceActivityDetailStudent::query()
                    ->where('distance_activity_detail_id', $previousDetail->id)
                    ->where('student_id', $studentId)
                    ->first();

            $videoOpenedAt = $previousStudentDetail?->video_opened_at;
            if (!$videoOpenedAt) {
                return __('You must open all previous video activities before completing later tasks.');
            }

            $lockMinutes = $this->videoCompletionLockMinutes();
            $unlockAt = Carbon::parse($videoOpenedAt)->addMinutes($lockMinutes);
            if (now()->lt($unlockAt)) {
                return __('You must wait :minutes minutes after opening a previous video before completing later tasks.', ['minutes' => $lockMinutes]);
            }
        }

        return null;
    }

    protected function validateDetailCompletionRequirements(
        DistanceActivityDetail $detail,
        DistanceActivityDetailStudent $studentDetail
    ): ?string {
        if ($detail->type->value === 'video') {
            if (!$studentDetail->video_opened_at) {
                return __('You must open the video before marking this task as completed.');
            }

            $unlockAt = $this->videoUnlockAt($studentDetail->video_opened_at);
            if ($unlockAt && now()->lt($unlockAt)) {
                return __('You must wait :minutes minutes after opening the video before marking this task as completed.', [
                    'minutes' => $this->videoCompletionLockMinutes(),
                ]);
            }
        }

        if (
            $detail->type->value === 'production'
            && $studentDetail->getMedia('student-production')->isEmpty()
        ) {
            return __('You must save your production before marking this task as completed.');
        }

        return null;
    }

    protected function nextCompletionLockedUntil(DistanceActivityDetail $detail, $orderedDetails, User $user): ?string
    {
        $student = $this->resolveStudent($user);
        if (! $student) {
            return null;
        }

        $currentStudentDetail = $detail->students->firstWhere('student_id', $student->id);
        if ($detail->type->value === 'video' && $currentStudentDetail?->video_opened_at) {
            $currentUnlockAt = $this->videoUnlockAt($currentStudentDetail->video_opened_at);
            if ($currentUnlockAt?->isFuture()) {
                return $currentUnlockAt->toISOString();
            }
        }

        $currentIndex = $orderedDetails->search(fn (DistanceActivityDetail $orderedDetail) => $orderedDetail->id === $detail->id);

        if (!is_int($currentIndex) || $currentIndex <= 0) {
            return null;
        }

        $lockUntil = null;

        foreach ($orderedDetails->slice(0, $currentIndex) as $previousDetail) {
            if ($previousDetail->type->value !== 'video') {
                continue;
            }

            $previousStudentDetail = $previousDetail->students->firstWhere('student_id', $student->id);
            if (!$previousStudentDetail?->video_opened_at) {
                return null;
            }

            $candidate = $this->videoUnlockAt($previousStudentDetail->video_opened_at);
            if ($candidate->isFuture() && ($lockUntil === null || $candidate->gt($lockUntil))) {
                $lockUntil = $candidate;
            }
        }

        return $lockUntil?->toISOString();
    }

    protected function completionLockMessage(DistanceActivityDetail $detail, $orderedDetails, User $user): ?string
    {
        $student = $this->resolveStudent($user);
        if (! $student) {
            return null;
        }

        $currentStudentDetail = $detail->students->firstWhere('student_id', $student->id);
        if ($detail->type->value === 'video' && $currentStudentDetail?->video_opened_at) {
            $currentUnlockAt = $this->videoUnlockAt($currentStudentDetail->video_opened_at);
            if ($currentUnlockAt?->isFuture()) {
                return __('You must wait :minutes minutes after opening the video before marking this task as completed.', [
                    'minutes' => $this->videoCompletionLockMinutes(),
                ]);
            }
        }

        $currentIndex = $orderedDetails->search(fn (DistanceActivityDetail $orderedDetail) => $orderedDetail->id === $detail->id);

        if (!is_int($currentIndex) || $currentIndex <= 0) {
            return null;
        }

        foreach ($orderedDetails->slice(0, $currentIndex) as $previousDetail) {
            if ($previousDetail->type->value !== 'video') {
                continue;
            }

            $previousStudentDetail = $previousDetail->students->firstWhere('student_id', $student->id);
            if (!$previousStudentDetail?->video_opened_at) {
                return __('Open the previous video activity first.');
            }

            $candidate = $this->videoUnlockAt($previousStudentDetail->video_opened_at);
            if ($candidate->isFuture()) {
                return __('You must wait :minutes minutes after opening a previous video before completing later tasks.', [
                    'minutes' => $this->videoCompletionLockMinutes(),
                ]);
            }
        }

        return null;
    }

    protected function videoUnlockAt($videoOpenedAt): ?Carbon
    {
        if (!$videoOpenedAt) {
            return null;
        }

        return Carbon::parse($videoOpenedAt)->addMinutes($this->videoCompletionLockMinutes());
    }

    public function videoCompletionLockMinutes(): int
    {
        return (int) config('academics.distance_activities.video_completion_lock_minutes', 1);
    }

    protected function syncDistanceActivityStudentCompletion(DistanceActivity $activity, int $studentId): void
    {
        $activity->loadMissing('details.students');
        $studentActivity = DistanceActivityStudent::query()->firstOrCreate(
            [
                'distance_activity_id' => $activity->id,
                'student_id' => $studentId,
            ],
            [
                'completed' => false,
                'completed_at' => null,
            ]
        );

        $detailRows = $activity->details->map(function (DistanceActivityDetail $detail) use ($studentId) {
            return $detail->students->firstWhere('student_id', $studentId)
                ?? DistanceActivityDetailStudent::query()
                    ->where('distance_activity_detail_id', $detail->id)
                    ->where('student_id', $studentId)
                    ->first();
        })->filter();

        $allCompleted = $detailRows->count() === $activity->details->count()
            && $detailRows->every(fn (DistanceActivityDetailStudent $detailStudent) => $detailStudent->completed);

        $studentActivity->update([
            'completed' => $allCompleted,
            'completed_at' => $allCompleted ? now() : null,
        ]);
    }

    protected function studentProductionMedia(DistanceActivityDetailStudent $detailStudent): array
    {
        return $detailStudent->getMedia('student-production')
            ->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'media_type' => $media->getCustomProperty('media_type'),
            ])
            ->values()
            ->all();
    }

    protected function detailStudentSubmissions(DistanceActivityDetail $detail): array
    {
        return $detail->students
            ->sortBy(fn (DistanceActivityDetailStudent $detailStudent) => $detailStudent->student?->profile?->full_name ?? '')
            ->map(fn (DistanceActivityDetailStudent $detailStudent) => [
                'id' => $detailStudent->id,
                'student_id' => $detailStudent->student_id,
                'student_name' => $detailStudent->student?->profile?->full_name
                    ?? $detailStudent->student?->profile?->email
                    ?? __('Student'),
                'completed' => (bool) $detailStudent->completed,
                'completed_at' => $detailStudent->completed_at,
                'video_opened_at' => $detailStudent->video_opened_at,
                'media' => $this->studentProductionMedia($detailStudent),
            ])
            ->values()
            ->all();
    }

    protected function activityStudentAssignments(DistanceActivity $activity): array
    {
        return $activity->students
            ->sortBy(fn (DistanceActivityStudent $studentActivity) => $studentActivity->student?->profile?->full_name ?? '')
            ->map(fn (DistanceActivityStudent $studentActivity) => [
                'id' => $studentActivity->id,
                'student_id' => $studentActivity->student_id,
                'student_name' => $studentActivity->student?->profile?->full_name
                    ?? $studentActivity->student?->profile?->email
                    ?? __('Student'),
                'completed' => (bool) $studentActivity->completed,
                'completed_at' => $studentActivity->completed_at,
            ])
            ->values()
            ->all();
    }

    protected function viewerMode(DistanceActivity $activity, User $user, ?Student $student = null): string
    {
        if ($user->can('view all distance activities')) {
            return 'manager';
        }

        $student ??= $this->resolveStudent($user);
        $isAssignedStudent = $student
            && $activity->students->firstWhere('student_id', $student->id);
        $isOwner = (int) $activity->user_id === (int) $user->id
            || (int) $activity->teacher?->profile?->user?->id === (int) $user->id;

        if ($isAssignedStudent && !$isOwner) {
            return 'student';
        }

        if ($isOwner || $user->can('view own distance activities')) {
            return 'manager';
        }

        return 'student';
    }

    protected function resolveStudent(User $user): ?Student
    {
        $user->loadMissing('profile.student');

        return $user->profile?->student;
    }
}
