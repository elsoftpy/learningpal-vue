<?php

namespace App\Http\Controllers\Selectable;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Academics\Settings\CourseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CourseListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = Course::query()->with(['language', 'languageLevel']);
        (new CourseVisibilityService())->applyCourseScope($query, $request->user(), 'id');
        $search = trim((string) ($request->input('search') ?? $request->input('params.search', '')));

        if ($search === '') {
            return $query->limit(10)->get()->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => (new CourseService())->getCourseDisplayName($course),
                ];  
            });
        }

        $query->where(function (Builder $query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
            ->orWhereHas('language', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('languageLevel', function ($q) use ($search) {
                $q->where('level', 'like', '%'.$search.'%');
            });
        });

        return $query->limit(10)->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => (new CourseService())->getCourseDisplayName($course),
            ];
        });
    }
}
