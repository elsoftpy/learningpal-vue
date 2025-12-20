<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassScheduleRequest;
use App\Models\ClassSchedule;
use App\Services\Academics\Lessons\ClassScheduleService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
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

        // Apply filters if any
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Pagination
        $paginated = $query->orderBy('schedule_month', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $classSchedules = $paginated->getCollection()->map(function (ClassSchedule $classSchedule) {
            return (new ClassScheduleService())->classScheduleData($classSchedule);
        });

        return ResponseService::success(
            data: [
                'class_schedules' => $classSchedules,
                'total' => $paginated->total(),
            ]
        );
    }

    public function classScheduleData(ClassSchedule $classSchedule, ClassScheduleService $classScheduleService)
    {
        $classScheduleData = $classScheduleService->classScheduleData($classSchedule);
        return ResponseService::success(
            data: ['class_schedule' => $classScheduleData]
        );
    }

    public function store(ClassScheduleRequest $request, ClassScheduleService $classScheduleService)
    {
        $classSchedule = $classScheduleService->createClassSchedule($request->validated());
        return ResponseService::success(
            message: __('Class schedule created successfully.'),
            data: ['class_schedule' => $classScheduleService->classScheduleData($classSchedule)]
        );
    }

    public function update(
        ClassScheduleRequest $request,
        ClassSchedule $classSchedule,
        ClassScheduleService $classScheduleService
    ) {
        $classSchedule->update($request->validated());
        return ResponseService::success(
            message: __('Class schedule updated successfully.'),
            data: ['class_schedule' => $classScheduleService->classScheduleData($classSchedule)]
        );
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
