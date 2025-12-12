<?php

namespace App\Http\Controllers\Settings\Languages;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $languages = Language::query();

        if ($search) {
            $languages->where('name', 'like', '%' . $search . '%');
        }

        $paginated = $languages->paginate($perPage, ['*'], 'page', $page);

        $languages = $paginated->getCollection()->map(function (Language $language) {
            return [
                'id' => $language->id,
                'name' => $language->name,
            ];
        });

        return ResponseService::success(
            data: [
                'languages' => $languages,
                'total' => $paginated->total(),
            ],
        );
    }
}
