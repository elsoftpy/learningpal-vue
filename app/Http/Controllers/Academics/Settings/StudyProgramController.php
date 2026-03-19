<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudyProgramRequest;
use App\Models\StudyProgram;
use App\Services\Academics\Settings\StudyProgramService;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Http\Request;

class StudyProgramController extends Controller
{
    use FilterResolverTrait;

    public function index(Request $request, StudyProgramService $studyProgramService)
    {
        $page = (int) $request->page;
        $perPage = (int) $request->per_page;
        $search = $request->search;
        $filters = $this->resolveFilters($request->filters);

        $studyProgramsQuery = StudyProgram::query()
            ->with(['languageLevel.language', 'weeks.activities']);

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
