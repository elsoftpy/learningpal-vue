<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassScheduleRequest;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Services\Academics\Lessons\ClassScheduleService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = $request->page;
        $perPage = $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        $allowedSortFields = ['id', 'name', 'schedule_month'];
        $sortField = $request->string('sort_field')->toString();
        $sortOrder = strtolower($request->string('sort_order', 'desc')->toString());

        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'schedule_month';
        }

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $query = ClassSchedule::query()
            ->with(['course', 'details']);

        if ($search) {
            if (str_contains($search, '/')) {
                $searchArray = explode('/', $search);
                if (count($searchArray) === 3) {
                    $search = $searchArray[2] . '-' . $searchArray[1] . '-' . $searchArray[0];
                } elseif (count($searchArray) === 2) {
                    $search = $searchArray[1] . '-' . $searchArray[0];
                }
            }

            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('schedule_month', 'like', '%' . $search . '%')
                ->orWhereHas('course', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }

        $paginated = $query->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $includeFeedback = $request->user()?->can('view schedule feedback') ?? false;
        $classScheduleService = new ClassScheduleService();

        $classSchedules = $paginated->getCollection()->map(function (ClassSchedule $classSchedule) use ($classScheduleService, $includeFeedback) {
            return $classScheduleService->classScheduleData($classSchedule, $includeFeedback);
        });

        return ResponseService::success(
            message: __('Class schedules retrieved successfully.'),
            data: [
                'class_schedules' => $classSchedules,
                'total' => $paginated->total(),
            ]
        );
    }

    public function classScheduleData(Request $request, ClassSchedule $classSchedule, ClassScheduleService $classScheduleService)
    {
        $classScheduleData = $classScheduleService->classScheduleData(
            $classSchedule,
            $request->user()?->can('view schedule feedback') ?? false
        );

        return ResponseService::success(
            message: __('Class schedule retrieved successfully.'),
            data: ['class_schedule' => $classScheduleData]
        );
    }

    public function store(ClassScheduleRequest $request, ClassScheduleService $classScheduleService)
    {
        $classSchedule = $classScheduleService->createClassSchedule($request->validated());

        return ResponseService::success(
            message: __('Class schedule created successfully.'),
            data: ['class_schedule' => $classScheduleService->classScheduleData(
                $classSchedule,
                $request->user()?->can('view schedule feedback') ?? false
            )]
        );
    }

    public function update(
        ClassScheduleRequest $request,
        ClassSchedule $classSchedule,
        ClassScheduleService $classScheduleService
    ) {
        $updateClassSchedule = $classScheduleService->updateClassSchedule($classSchedule, $request->validated());

        return ResponseService::success(
            message: __('Class schedule updated successfully.'),
            data: ['class_schedule' => $classScheduleService->classScheduleData(
                $updateClassSchedule,
                $request->user()?->can('view schedule feedback') ?? false
            )]
        );
    }

    public function updateFeedback(
        Request $request,
        ClassSchedule $classSchedule,
        ClassScheduleService $classScheduleService
    ) {
        $validated = $request->validate([
            'feedback' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $feedback = $validated['feedback'] ?? null;
        $feedback = is_string($feedback) ? trim($feedback) : null;

        $existingFeedback = is_string($classSchedule->feedback) ? trim($classSchedule->feedback) : null;
        $hasExistingFeedback = $existingFeedback !== null && $existingFeedback !== '';
        $hasIncomingFeedback = $feedback !== null && $feedback !== '';

        if ($hasExistingFeedback && !$hasIncomingFeedback) {
            $this->authorizeFeedbackAction($request, 'delete schedule feedback');
        } elseif (!$hasExistingFeedback && $hasIncomingFeedback) {
            $this->authorizeFeedbackAction($request, 'create schedule feedback');
        } elseif ($hasExistingFeedback && $hasIncomingFeedback && $existingFeedback !== $feedback) {
            $this->authorizeFeedbackAction($request, 'edit schedule feedback');
        } else {
            $this->authorizeFeedbackAction($request, 'view schedule feedback');
        }

        $updatedClassSchedule = $classScheduleService->updateClassSchedule($classSchedule, [
            'feedback' => $hasIncomingFeedback ? $feedback : null,
        ]);

        return ResponseService::success(
            message: __('Class schedule feedback updated successfully.'),
            data: ['class_schedule' => $classScheduleService->classScheduleData(
                $updatedClassSchedule,
                $request->user()?->can('view schedule feedback') ?? false
            )]
        );
    }

    protected function authorizeFeedbackAction(Request $request, string $permission): void
    {
        if (!($request->user()?->can('view schedule feedback') ?? false)) {
            throw new AuthorizationException(__('This action is unauthorized.'));
        }

        if (!($request->user()?->can($permission) ?? false)) {
            throw new AuthorizationException(__('This action is unauthorized.'));
        }
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        $classSchedule->details()->delete();
        $classSchedule->delete();
        return ResponseService::success(
            message: __('Class schedule deleted successfully.')
        );
    }
}
