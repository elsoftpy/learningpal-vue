<?php

namespace App\Http\Controllers\Selectable;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Authorization\CourseVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LanguageListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = Language::query()
            ->select('id', 'name');

        $visibleLanguageLevelIds = (new CourseVisibilityService())->visibleLanguageLevelIdsForUser($request->user());

        if ($visibleLanguageLevelIds !== null) {
            $query->whereHas('levels', function (Builder $levelQuery) use ($visibleLanguageLevelIds) {
                $levelQuery->whereIn('id', $visibleLanguageLevelIds);
            });
        }

        return $query->get();
    }
}
