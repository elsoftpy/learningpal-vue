<?php

namespace App\Http\Controllers\Academics\Lessons;

use App\Http\Controllers\Controller;
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
            $query->where('name', 'like', '%' . $search . '%')
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
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

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
}
