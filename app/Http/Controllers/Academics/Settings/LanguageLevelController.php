<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\LanguageLevelRequest;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Services\Academics\Settings\LanguageLevelService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class LanguageLevelController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort(
            $request,
            ['id', 'description', 'level', 'language_name', 'status'],
            'level'
        );

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

        if ($sortField === 'language_name') {
            $languageLevelsQuery->orderBy(
                Language::query()
                    ->select('name')
                    ->whereColumn('languages.id', 'language_levels.language_id')
                    ->limit(1),
                $sortOrder
            );
        } else {
            $languageLevelsQuery->orderBy($sortField, $sortOrder);
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

    public function languageLevelData(LanguageLevel $languageLevel, LanguageLevelService $languageLevelService)
    {
        $languageLevelData = $languageLevelService->languageLevelData($languageLevel);

        return ResponseService::success(
            data: [
                'language_level' => $languageLevelData,
            ],
        );
    }

    public function store(LanguageLevelRequest $request, LanguageLevelService $languageLevelService)
    {
        $languageLevel = LanguageLevel::create($request->validated());

        $languageLevelData = $languageLevelService->languageLevelData($languageLevel);

        return ResponseService::success(
            data: [
                'language_level' => $languageLevelData,
            ],
            message: 'Language level created successfully.',
        );
    }

    public function update(LanguageLevelRequest $request, LanguageLevel $languageLevel, LanguageLevelService $languageLevelService)
    {
        $languageLevel->update($request->validated());

        $languageLevelData = $languageLevelService->languageLevelData($languageLevel);

        return ResponseService::success(
            data: [
                'language_level' => $languageLevelData,
            ],
            message: 'Language level updated successfully.',
        );
    }

    public function destroy(LanguageLevel $languageLevel)
    {
        $languageLevel->delete();

        return ResponseService::success(
            message: 'Language level deleted successfully.',
        );
    }
}
