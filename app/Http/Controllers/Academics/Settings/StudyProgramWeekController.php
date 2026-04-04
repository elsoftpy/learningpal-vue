<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudyProgramWeekRequest;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use App\Services\Academics\Settings\StudyProgramService;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Utilities\ResponseService;

class StudyProgramWeekController extends Controller
{
    public function createData(StudyProgram $studyProgram, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $studyProgram->language_level_id);

        return ResponseService::success(
            data: [
                'study_program' => $studyProgramService->studyProgramData($studyProgram),
            ],
        );
    }

    public function store(StudyProgramWeekRequest $request, StudyProgram $studyProgram, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId($request->user(), $studyProgram->language_level_id);

        $week = $studyProgramService->createStudyProgramWeek($studyProgram, $request->validated());

        return ResponseService::success(
            message: 'Study program week created successfully.',
            data: [
                'week' => $studyProgramService->studyProgramWeekData($week),
            ],
        );
    }

    public function data(StudyProgramWeek $week, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $week->studyProgram?->language_level_id);

        return ResponseService::success(
            data: [
                'week' => $studyProgramService->studyProgramWeekData($week),
            ],
        );
    }

    public function update(StudyProgramWeekRequest $request, StudyProgramWeek $week, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId($request->user(), $week->studyProgram?->language_level_id);

        $week = $studyProgramService->updateStudyProgramWeek($week, $request->validated());

        return ResponseService::success(
            message: 'Study program week updated successfully.',
            data: [
                'week' => $studyProgramService->studyProgramWeekData($week),
            ],
        );
    }

    public function destroy(StudyProgramWeek $week, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $week->studyProgram?->language_level_id);

        if ($week->studyProgram()->withCount('weeks')->first()?->weeks_count <= 1) {
            return ResponseService::failedValidationResponse(
                errors: ['week' => [__('A study program must contain at least one week.')]],
                message: __('Unable to delete the study program week.')
            );
        }

        $studyProgramService->deleteStudyProgramWeek($week);

        return ResponseService::success(
            message: 'Study program week deleted successfully.',
        );
    }
}
