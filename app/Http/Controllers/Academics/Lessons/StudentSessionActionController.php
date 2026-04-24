<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Http\Controllers\Controller;
use App\Models\ClassScheduleDetail;
use App\Services\Academics\Lessons\ClassScheduleDetailService;
use App\Services\Academics\Lessons\ClassSessionActionService;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentSessionActionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('academics/classes/class-schedules/StudentSessionsPage');
    }

    public function list(Request $request): JsonResponse
    {
        $visibleCourseIds = (new CourseVisibilityService)->visibleCourseIdsForUser($request->user());

        $details = ClassScheduleDetail::query()
            ->with(['classSchedule', 'classSchedule.course'])
            ->whereHas('classSchedule.course')
            ->when(
                $visibleCourseIds !== null,
                fn ($q) => $q->whereHas('classSchedule', fn ($q2) => $q2->whereIn('course_id', $visibleCourseIds))
            )
            ->orderByDesc('session_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(fn (ClassScheduleDetail $detail) => (new ClassScheduleDetailService)->sessionData($detail));

        return response()->json(['sessions' => $details]);
    }

    public function performAction(Request $request, ClassScheduleDetail $detail): JsonResponse
    {
        $validated = $request->validate([
            'action_type' => ['required', 'string', 'in:pending,upload_task'],
        ]);

        $user = $request->user();
        $studentModel = $user->profile?->student;

        if (! $studentModel) {
            abort(403);
        }

        $detail->loadMissing([
            'classSchedule.course.students.profile.user',
            'classSchedule.course.teachers.profile.user',
        ]);

        $course = $detail->classSchedule?->course;

        if (! $course || ! $course->students->contains('id', $studentModel->id)) {
            abort(403);
        }

        $actionableStatuses = ['scheduled', 'reprogramed'];

        if (! in_array($detail->status, $actionableStatuses, true)) {
            return response()->json([
                'message' => __('This session cannot be modified at this time.'),
            ], 422);
        }

        $performed = (new ClassSessionActionService)->performAction($detail, $studentModel, $validated['action_type']);

        if (! $performed) {
            return response()->json([
                'message' => __('An action has already been registered for this session.'),
            ], 422);
        }

        return ResponseService::success(message: __('Action registered successfully.'));
    }

    public function statusHistory(Request $request, ClassScheduleDetail $detail): JsonResponse
    {
        $visibleCourseIds = (new CourseVisibilityService)->visibleCourseIdsForUser($request->user());

        $courseId = $detail->classSchedule?->course_id;

        if ($visibleCourseIds !== null && ! in_array($courseId, $visibleCourseIds, true)) {
            abort(403);
        }

        $history = $detail->statusHistories()
            ->with('changedByUser.profile')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'changed_by_type' => $entry->changed_by_type,
                'changed_by_name' => $entry->changedByUser?->profile?->full_name
                    ?? $entry->changedByUser?->email
                    ?? ucfirst($entry->changed_by_type),
                'old_status' => $entry->old_status,
                'new_status' => $entry->new_status,
                'action_type' => $entry->action_type,
                'created_at' => $entry->created_at?->toIso8601String(),
            ]);

        return response()->json(['history' => $history]);
    }
}
