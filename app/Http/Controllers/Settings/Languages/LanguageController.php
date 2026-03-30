<?php

namespace App\Http\Controllers\Settings\Languages;

use App\Http\Controllers\Controller;
use App\Http\Requests\LanguageRequest;
use App\Models\Language;
use App\Services\Settings\Languages\LanguageService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort($request, ['id', 'name'], 'name');

        $languagesQuery = Language::query();

        if ($search) {
            $languagesQuery->where('name', 'like', '%' . $search . '%');
        }

        $paginated = $languagesQuery
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $languages = $paginated->getCollection()->map(function (Language $language) {
            return (new LanguageService())->languageData($language);
        });

        return ResponseService::success(
            data: [
                'languages' => $languages,
                'total' => $paginated->total(),
            ],
        );
    }

    public function store(LanguageRequest $request, LanguageService $languageService)
    {
        $language = Language::create([
            'name' => $request->name,
        ]);

        $languageData = $languageService->languageData($language);

        return ResponseService::success(
            data: [
                'language' => $languageData,
            ],
            message: 'Language created successfully.',
        );
    }

    public function languageData(Language $language, LanguageService $languageService)
    {
        $languageData = $languageService->languageData($language);

        return ResponseService::success(
            data: [
                'language' => $languageData,
            ],
        );
    }

    public function update(LanguageRequest $request, Language $language, LanguageService $languageService)
    {
        $language->update([
            'name' => $request->name,
        ]);

        $languageData = $languageService->languageData($language);

        return ResponseService::success(
            data: [
                'language' => $languageData,
            ],
            message: 'Language updated successfully.',
        );
    }

    public function destroy(Language $language)
    {
        try {
            $language->delete();
        } catch (QueryException $exception) {
            return ResponseService::error(
                message: __('This language cannot be deleted because it is currently in use.'),
                statusCode: 422,
            );
        }

        return ResponseService::success(
            message: 'Language deleted successfully.',
        );
    }
}
