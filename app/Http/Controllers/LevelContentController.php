<?php

namespace App\Http\Controllers;

use App\Http\Requests\LevelContentRequest;
use App\Models\LevelContent;
use App\Services\Academics\Settings\LevelContentService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;
use Monolog\Level;

class LevelContentController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $levelContentQuery = LevelContent::query()
            ->with('languageLevel');

        if ($search) {
            $levelContentQuery->where('content', 'like', '%' . $search . '%')
                ->orWhereHas('languageLevel', function ($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                      ->orWhere('level', 'like', '%' . $search . '%');
                });
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $levelContentQuery->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        $paginated = $levelContentQuery->paginate($perPage, ['*'], 'page', $page);

        $levelContent = $paginated->getCollection()->map(function (LevelContent $levelContent) {
            return (new LevelContentService())->levelContentData($levelContent);
        });

        return ResponseService::success(
            data: [
                'level_contents' => $levelContent,
                'total' => $paginated->total(),
            ],
        );
    }

    public function store(LevelContentRequest $request, LevelContentService $levelContentService)
    {
        $data = $request->validated();

        $levelContentService = new LevelContentService();
        $levelContent = $levelContentService->createLevelContent($data);

        return ResponseService::success(
            message: 'Level content created successfully.',
            data: [
                'level_content' => $levelContentService->levelContentData($levelContent),
            ],
        );
    }

    public function levelContentData(LevelContent $levelContent, LevelContentService $levelContentService)
    {
        $levelContentData = $levelContentService->levelContentData($levelContent);

        return ResponseService::success(
            data: [
                'level_content' => $levelContentData,
            ],
        );
    }

    public function update(LevelContentRequest $request, LevelContent $levelContent, LevelContentService $levelContentService)
    {
        $data = $request->validated();

        $levelContent = $levelContentService->updateLevelContent($levelContent, $data);

        return ResponseService::success(
            message: 'Level content updated successfully.',
            data: [
                'level_content' => $levelContentService->levelContentData($levelContent),
            ],
        );
    }

    public function destroy(LevelContent $levelContent)
    {
        $levelContent->delete();

        return ResponseService::success(
            message: 'Level content deleted successfully.',
        );
    }
}