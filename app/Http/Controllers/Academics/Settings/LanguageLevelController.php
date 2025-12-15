<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Models\LanguageLevel;
use App\Services\Academics\Settings\LanguageLevelService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class LanguageLevelController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $languageLevelsQuery = LanguageLevel::query()
            ->with('language');

        if ($search) {
            $languageLevelsQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('level', 'like', '%' . $search . '%')
                ->orWhereHas('language', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $languageLevelsQuery->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        $paginated = $languageLevelsQuery->paginate($perPage, ['*'], 'page', $page);

        $languageLevels = $paginated->getCollection()->map(function (LanguageLevel $languageLevel) {
            return (new LanguageLevelService())->languageLevelData($languageLevel);
        });

        return ResponseService::success(
            data: [
                'language_levels' => $languageLevels,
                'total' => $paginated->total(),
            ],
        );
    }
}
