<?php

namespace App\Http\Controllers\Selectable;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\LanguageLevel;
use App\Services\Authorization\CourseVisibilityService;
use Illuminate\Http\Request;

class LanguageLanguageLevelListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $languageId = $request->language_id;

        $query = LanguageLevel::query()
            ->select('id', 'description', 'level')
            ->where('language_id', $languageId)
            ->where('status', StatusEnum::ACTIVE->value);

        (new CourseVisibilityService())->applyLanguageLevelScope($query, $request->user(), 'id');

        return $query->get()
            ->map(function (LanguageLevel $level) {
                return [
                    'id' => $level->id,
                    'name' => $level->description.' - '.$level->level,
                ];
            });
    }
}
