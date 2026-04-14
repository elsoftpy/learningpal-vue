<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Http\Controllers\Controller;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityDetail;
use App\Models\DistanceActivityDetailStudent;
use App\Models\Course;
use App\Models\Language;
use App\Models\Profile;
use App\Models\StudyProgramWeek;
use App\Services\Academics\Lessons\DistanceActivityService;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DistanceActivityController extends Controller
{
    use SortResolverTrait;

    public function index(Request $request, DistanceActivityService $distanceActivityService)
    {
        $user = $request->user();

        if (!$distanceActivityService->canViewAny($user)) {
            return ResponseService::unauthorized(__('You are not authorized to view distance activities.'));
        }

        $page = (int) $request->page;
        $perPage = (int) ($request->per_page ?: 10);
        $search = trim((string) $request->search);
        [$sortField, $sortOrder] = $this->resolveSort(
            $request,
            ['id', 'teacher_name', 'course_name', 'title', 'week_number'],
            'week_number',
            'asc'
        );

        $query = $distanceActivityService->visibleActivitiesQuery($user);

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('comments', 'like', '%'.$search.'%')
                    ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('teacher.profile', fn ($teacherQuery) => $teacherQuery->where('full_name', 'like', '%'.$search.'%'))
                    ->orWhereHas('user.profile', fn ($ownerQuery) => $ownerQuery->where('full_name', 'like', '%'.$search.'%'));
            });
        }

        if ($sortField === 'teacher_name') {
            $query->orderBy(
                Profile::query()
                    ->select('profiles.full_name')
                    ->join('teachers', 'teachers.profile_id', '=', 'profiles.id')
                    ->whereColumn('teachers.id', 'distance_activities.teacher_id')
                    ->limit(1),
                $sortOrder
            );
        } elseif ($sortField === 'course_name') {
            $query->orderBy(
                Course::query()
                    ->select('name')
                    ->whereColumn('courses.id', 'distance_activities.course_id')
                    ->limit(1),
                $sortOrder
            );
        } elseif ($sortField === 'week_number') {
            $query->orderBy(
                StudyProgramWeek::query()
                    ->select('week_number')
                    ->whereColumn('study_program_weeks.id', 'distance_activities.study_program_week_id')
                    ->limit(1),
                $sortOrder
            );
            $query->orderBy('distance_activities.id', 'asc');
        } else {
            $query->orderBy($sortField, $sortOrder);
        }

        $paginated = $query
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginated->getCollection()
            ->map(fn (DistanceActivity $activity) => $distanceActivityService->listData($activity, $user))
            ->values();

        return ResponseService::success(
            message: __('Distance activities retrieved successfully.'),
            data: [
                'distance_activities' => $items,
                'total' => $paginated->total(),
            ]
        );
    }

    public function data(Request $request, DistanceActivity $distanceActivity, DistanceActivityService $distanceActivityService)
    {
        $activity = $distanceActivityService->findAccessibleActivity($distanceActivity, $request->user());
        if (!$activity) {
            return ResponseService::unauthorized(__('You are not authorized to view this distance activity.'));
        }

        return ResponseService::success(
            message: __('Distance activity retrieved successfully.'),
            data: [
                'distance_activity' => $distanceActivityService->detailData($activity, $request->user()),
            ]
        );
    }

    public function completeDetail(Request $request, DistanceActivityDetail $detail, DistanceActivityService $distanceActivityService)
    {
        $validated = $request->validate([
            'completed' => ['nullable', 'boolean'],
        ]);

        try {
            $activity = DB::transaction(function () use ($detail, $request, $validated, $distanceActivityService) {
                return $distanceActivityService->markDetailCompletion(
                    $detail,
                    $request->user(),
                    $validated['completed'] ?? true
                );
            });
        } catch (\RuntimeException $exception) {
            return ResponseService::failedValidationResponse(
                errors: ['completed' => [$exception->getMessage()]],
                message: __('Unable to update distance activity completion.')
            );
        }

        if (!$activity) {
            return ResponseService::unauthorized(__('You are not authorized to update this distance activity task.'));
        }

        return ResponseService::success(
            message: __('Distance activity task updated successfully.'),
            data: [
                'distance_activity' => $distanceActivityService->detailData($activity, $request->user()),
            ]
        );
    }

    public function recordVideoOpen(Request $request, DistanceActivityDetail $detail, DistanceActivityService $distanceActivityService)
    {
        $studentDetail = $distanceActivityService->recordVideoOpen($detail, $request->user());
        if (!$studentDetail) {
            return ResponseService::unauthorized(__('You are not authorized to update this video activity.'));
        }

        return ResponseService::success(
            message: __('Video activity started successfully.'),
            data: [
                'video_opened_at' => $studentDetail->video_opened_at,
                'unlock_at' => $studentDetail->video_opened_at?->copy()->addMinutes(
                    $distanceActivityService->videoCompletionLockMinutes()
                ),
            ]
        );
    }

    public function saveStudentProduction(Request $request, DistanceActivityDetail $detail, DistanceActivityService $distanceActivityService)
    {
        $validated = $request->validate(
            [
                'student_production_file' => [
                    'nullable',
                    'file',
                    'max:10240',
                    'mimes:pdf,doc,docx,xls,xlsx,txt,jpeg,jpg,png,webp',
                ],
                'student_production_audio' => [
                    'nullable',
                    'file',
                    'max:10240',
                    'mimetypes:audio/mpeg,audio/mp3,audio/webm,audio/webm;codecs=opus,audio/ogg,video/webm',
                ],
            ],
            [
                'student_production_audio.mimetypes' => 'El campo student production audio debe ser un archivo de tipo: audio/mpeg, audio/mp3, audio/webm, audio/ogg.',
            ]
        );

        $hasAnyFile = !empty($validated['student_production_file']) || !empty($validated['student_production_audio']);
        if (!$hasAnyFile) {
            return ResponseService::failedValidationResponse(
                errors: ['student_production' => [__('No student production files were provided.')]],
                message: __('Unable to save student production.')
            );
        }

        $studentDetail = DB::transaction(function () use ($distanceActivityService, $detail, $request) {
            return $distanceActivityService->saveStudentProduction(
                $detail,
                $request->user(),
                [
                    'student_production_file' => $request->file('student_production_file'),
                    'student_production_audio' => $request->file('student_production_audio'),
                ]
            );
        });

        if (!$studentDetail) {
            return ResponseService::unauthorized(__('You are not authorized to upload student production for this task.'));
        }

        $activity = $distanceActivityService->findAccessibleActivity($detail->distanceActivity, $request->user());

        return ResponseService::success(
            message: __('Student production saved successfully.'),
            data: [
                'distance_activity' => $activity ? $distanceActivityService->detailData($activity, $request->user()) : null,
            ]
        );
    }

    public function updateManagedDetailCompletion(
        Request $request,
        DistanceActivityDetailStudent $detailStudent,
        DistanceActivityService $distanceActivityService
    ) {
        $validated = $request->validate([
            'completed' => ['required', 'boolean'],
        ]);

        $activity = DB::transaction(function () use ($detailStudent, $request, $validated, $distanceActivityService) {
            return $distanceActivityService->updateManagedDetailCompletion(
                $detailStudent,
                $request->user(),
                (bool) $validated['completed']
            );
        });

        if (!$activity) {
            return ResponseService::unauthorized(__('You are not authorized to update this distance activity task.'));
        }

        return ResponseService::success(
            message: __('Distance activity task updated successfully.'),
            data: [
                'distance_activity' => $distanceActivityService->detailData($activity, $request->user()),
            ]
        );
    }

    public function deleteStudentSubmission(
        Request $request,
        DistanceActivityDetailStudent $detailStudent,
        Media $media,
        DistanceActivityService $distanceActivityService
    ) {
        $activity = DB::transaction(function () use ($detailStudent, $media, $request, $distanceActivityService) {
            return $distanceActivityService->deleteStudentSubmissionMedia(
                $detailStudent,
                $media,
                $request->user()
            );
        });

        if (!$activity) {
            return ResponseService::unauthorized(__('You are not authorized to delete this distance activity submission.'));
        }

        return ResponseService::success(
            message: __('Student production deleted successfully.'),
            data: [
                'distance_activity' => $distanceActivityService->detailData($activity, $request->user()),
            ]
        );
    }
}
