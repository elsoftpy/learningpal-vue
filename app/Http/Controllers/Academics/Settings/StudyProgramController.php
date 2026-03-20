<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudyProgramRequest;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeekActivity;
use App\Services\Academics\Settings\StudyProgramService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Traits\SortResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class StudyProgramController extends Controller
{
    use FilterResolverTrait, SortResolverTrait;

    public function index(Request $request, StudyProgramService $studyProgramService)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);
        [$sortField, $sortOrder] = $this->resolveSort(
            $request,
            ['id', 'title', 'language', 'level', 'weeks_count', 'activities_count', 'status'],
            'id',
            'desc'
        );

        $studyProgramsQuery = StudyProgram::query()
            ->with(['languageLevel.language', 'weeks.activities'])
            ->withCount('weeks');

        if ($search) {
            $studyProgramsQuery->where('title', 'like', '%'.$search.'%')
                ->orWhereHas('languageLevel', function ($query) use ($search) {
                    $query->where('description', 'like', '%'.$search.'%')
                        ->orWhere('level', 'like', '%'.$search.'%')
                        ->orWhereHas('language', function ($languageQuery) use ($search) {
                            $languageQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $studyProgramsQuery->where($filter['field'], $filter['operator'], $filter['value']);
            }
        }

        switch ($sortField) {
            case 'language':
                $studyProgramsQuery->orderBy(
                    Language::query()
                        ->select('languages.name')
                        ->join('language_levels', 'language_levels.language_id', '=', 'languages.id')
                        ->whereColumn('language_levels.id', 'study_programs.language_level_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            case 'level':
                $studyProgramsQuery->orderBy(
                    LanguageLevel::query()
                        ->select('level')
                        ->whereColumn('language_levels.id', 'study_programs.language_level_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
            case 'weeks_count':
                $studyProgramsQuery->orderBy('weeks_count', $sortOrder);
                break;
            case 'activities_count':
                $studyProgramsQuery->orderBy(
                    StudyProgramWeekActivity::query()
                        ->selectRaw('count(*)')
                        ->join('study_program_weeks', 'study_program_weeks.id', '=', 'study_program_week_activities.study_program_week_id')
                        ->whereColumn('study_program_weeks.study_program_id', 'study_programs.id'),
                    $sortOrder
                );
                break;
            default:
                $studyProgramsQuery->orderBy($sortField, $sortOrder);
                break;
        }

        $paginated = $studyProgramsQuery->paginate($perPage, ['*'], 'page', $page);

        $studyPrograms = $paginated->getCollection()->map(
            fn (StudyProgram $studyProgram) => $studyProgramService->studyProgramData($studyProgram)
        );

        return ResponseService::success(
            data: [
                'study_programs' => $studyPrograms,
                'total' => $paginated->total(),
            ],
        );
    }

    public function store(StudyProgramRequest $request, StudyProgramService $studyProgramService)
    {
        $studyProgram = $studyProgramService->createStudyProgram($request->validated());

        return ResponseService::success(
            message: 'Study program created successfully.',
            data: [
                'study_program' => $studyProgramService->studyProgramData($studyProgram),
            ],
        );
    }

    public function studyProgramData(StudyProgram $studyProgram, StudyProgramService $studyProgramService)
    {
        return ResponseService::success(
            data: [
                'study_program' => $studyProgramService->studyProgramData($studyProgram),
            ],
        );
    }

    public function update(StudyProgramRequest $request, StudyProgram $studyProgram, StudyProgramService $studyProgramService)
    {
        $studyProgram = $studyProgramService->updateStudyProgram($studyProgram, $request->validated());

        return ResponseService::success(
            message: 'Study program updated successfully.',
            data: [
                'study_program' => $studyProgramService->studyProgramData($studyProgram),
            ],
        );
    }

    public function destroy(StudyProgram $studyProgram, StudyProgramService $studyProgramService)
    {
        $studyProgramService->deleteStudyProgram($studyProgram);

        return ResponseService::success(
            message: 'Study program deleted successfully.',
        );
    }
}
