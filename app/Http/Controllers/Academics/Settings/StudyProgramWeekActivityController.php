<?php

namespace App\Http\Controllers\Academics\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudyProgramWeekActivityRequest;
use App\Models\StudyProgramWeek;
use App\Models\StudyProgramWeekActivity;
use App\Services\Academics\Settings\StudyProgramService;
use App\Services\Authorization\CourseVisibilityService;
use App\Services\Utilities\ResponseService;

class StudyProgramWeekActivityController extends Controller
{
    public function createData(StudyProgramWeek $week, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $week->studyProgram?->language_level_id);

        return ResponseService::success(
            data: [
                'week' => $studyProgramService->studyProgramWeekActivityCreateData($week),
            ],
        );
    }

    public function store(StudyProgramWeekActivityRequest $request, StudyProgramWeek $week, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId($request->user(), $week->studyProgram?->language_level_id);

        $activity = $studyProgramService->createStudyProgramWeekActivity(
            $week,
            $request->validated(),
            $request->file('study_material')
        );

        return ResponseService::success(
            message: 'Study program activity created successfully.',
            data: [
                'activity' => $studyProgramService->studyProgramWeekActivityData($activity),
            ],
        );
    }

    public function data(StudyProgramWeekActivity $activity, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $activity->studyProgramWeek?->studyProgram?->language_level_id);

        return ResponseService::success(
            data: [
                'activity' => $studyProgramService->studyProgramWeekActivityData($activity),
            ],
        );
    }

    public function update(StudyProgramWeekActivityRequest $request, StudyProgramWeekActivity $activity, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId($request->user(), $activity->studyProgramWeek?->studyProgram?->language_level_id);

        $activity = $studyProgramService->updateStudyProgramWeekActivity(
            $activity,
            $request->validated(),
            $request->file('study_material')
        );

        return ResponseService::success(
            message: 'Study program activity updated successfully.',
            data: [
                'activity' => $studyProgramService->studyProgramWeekActivityData($activity),
            ],
        );
    }

    public function destroy(StudyProgramWeekActivity $activity, StudyProgramService $studyProgramService)
    {
        (new CourseVisibilityService())->authorizeLanguageLevelId(request()->user(), $activity->studyProgramWeek?->studyProgram?->language_level_id);

        if ($activity->studyProgramWeek()->withCount('activities')->first()?->activities_count <= 1) {
            return ResponseService::failedValidationResponse(
                errors: ['activity' => [__('A week must contain at least one activity.')]],
                message: __('Unable to delete the study program activity.')
            );
        }

        $studyProgramService->deleteStudyProgramWeekActivity($activity);

        return ResponseService::success(
            message: 'Study program activity deleted successfully.',
        );
    }
}
