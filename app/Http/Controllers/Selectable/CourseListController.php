<?php

namespace App\Http\Controllers\Selectable;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Academics\Settings\CourseService;
use Illuminate\Http\Request;

class CourseListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = Course::query();

        if ($request?->search === '') {
            return $query->limit(10)->get()->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => (new CourseService())->getCourseDisplayName($course),
                ];  
            });
        }

        $query->where('name', 'like', '%'.$request->search.'%')
            ->orWhereHas('language', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })
            ->orWhereHas('languageLevel', function ($q) use ($request) {
                $q->where('level', 'like', '%'.$request->search.'%');
            });

        return $query->limit(10)->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => (new CourseService())->getCourseDisplayName($course),
            ];
        });
    }
}
