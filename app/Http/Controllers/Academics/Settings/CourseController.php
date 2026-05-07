<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Services\Academics\Settings\CourseService;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request)
    {
        $visibility = new CourseVisibilityService;
        $page = $request->page;
        $perPage = $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort(
            $request,
            ['id', 'name', 'language_name', 'language_level', 'status'],
            'id'
        );

        $coursesQuery = Course::query()
            ->with(['language', 'languageLevel']);

        $visibility->applyCourseScope($coursesQuery, $request->user(), 'id');

        if ($search) {
            $coursesQuery->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('language', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('languageLevel', function ($q) use ($search) {
                        $q->where('level', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $coursesQuery->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        switch ($sortField) {
            case 'language_name':
                $coursesQuery->orderBy(
                    Language::query()
                        ->select('name')
                        ->whereColumn('languages.id', 'courses.language_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            case 'language_level':
                $coursesQuery->orderBy(
                    LanguageLevel::query()
                        ->select('level')
                        ->whereColumn('language_levels.id', 'courses.language_level_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            default:
                $coursesQuery->orderBy($sortField, $sortOrder);
                break;
        }

        $paginated = $coursesQuery->paginate($perPage, ['*'], 'page', $page);

        $courses = $paginated->getCollection()->map(function (Course $course) {
            return (new CourseService)->courseData($course);
        });

        return ResponseService::success(
            data: [
                'courses' => $courses,
                'total' => $paginated->total(),
            ],
        );
    }

    public function courseData(Course $course, CourseService $courseService)
    {
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $course->id);

        $courseData = $courseService->courseData($course);

        return ResponseService::success(
            data: [
                'course' => $courseData,
            ],
        );
    }

    public function store(CourseRequest $request, CourseService $courseService)
    {
        $course = $courseService->createCourse($request->validated(), $request->user());

        $courseData = $courseService->courseData($course);

        return ResponseService::success(
            message: 'Course created successfully.',
            data: [
                'course' => $courseData,
            ],
        );
    }

    public function update(CourseRequest $request, Course $course, CourseService $courseService)
    {
        (new CourseVisibilityService)->authorizeCourseId($request->user(), $course->id);

        $course = $courseService->updateCourse($course, $request->validated(), $request->user());

        $courseData = $courseService->courseData($course);

        return ResponseService::success(
            message: 'Course updated successfully.',
            data: [
                'course' => $courseData,
            ],
        );
    }

    public function destroy(Course $course)
    {
        (new CourseVisibilityService)->authorizeCourseId(request()->user(), $course->id);

        $course->delete();

        return ResponseService::success(
            message: 'Course deleted successfully.',
        );
    }
}
