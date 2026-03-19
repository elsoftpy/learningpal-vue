<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Services\Academics\Settings\CourseService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = $request->page;
        $perPage = $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $coursesQuery = Course::query()
            ->with(['language', 'languageLevel']);

        if ($search) {
            $coursesQuery->where('name', 'like', '%' . $search . '%')
                ->orWhereHas('language', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('languageLevel', function ($q) use ($search) {
                    $q->where('level', 'like', '%' . $search . '%');
                });
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $coursesQuery->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        $paginated = $coursesQuery->paginate($perPage, ['*'], 'page', $page);

        $courses = $paginated->getCollection()->map(function (Course $course){
            return (new CourseService())->courseData($course);
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
        $course->update($request->validated());

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
        $course->delete();

        return ResponseService::success(
            message: 'Course deleted successfully.',
        );
    }
}
