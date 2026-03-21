<?php

use App\Http\Controllers\Academics\Lessons\CalendarController;
use App\Http\Controllers\Academics\Lessons\ClassRecordController;
use App\Http\Controllers\Academics\Lessons\ClassScheduleController;
use App\Http\Controllers\Academics\Lessons\ClassScheduleDetailController;
use App\Http\Controllers\Academics\Lessons\DistanceActivityController;
use App\Http\Controllers\Academics\Reports\MonthlyClassesReportController;
use App\Http\Controllers\Academics\Reports\TeacherHoursReportController;
use App\Http\Controllers\Academics\Settings\CourseController;
use App\Http\Controllers\Academics\Settings\LanguageLevelController;
use App\Http\Controllers\Academics\Settings\StudyProgramController;
use App\Http\Controllers\Academics\Settings\StudyProgramWeekActivityController;
use App\Http\Controllers\Academics\Settings\StudyProgramWeekController;
use App\Http\Controllers\Academics\Settings\StudentController;
use App\Http\Controllers\Academics\Settings\TeacherController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\ClassReminderActionController;
use App\Http\Controllers\LevelContentController;
use App\Http\Controllers\Selectable\CourseListController;
use App\Http\Controllers\Selectable\LanguageLanguageLevelListController;
use App\Http\Controllers\Selectable\LanguageListController;
use App\Http\Controllers\Selectable\RoleListController;
use App\Http\Controllers\Selectable\StatusListController;
use App\Http\Controllers\Settings\Languages\LanguageController;
use App\Http\Controllers\Settings\Users\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware(['guest'])->group(function () {

        Route::post('/register', [AuthenticationController::class, 'register'])
            ->name('register');

        Route::post('/login', [AuthenticationController::class, 'login'])
            ->name('login');
    });

    Route::middleware(['auth'])->group(function () {

        Route::post('/logout', [AuthenticationController::class, 'logout'])
            ->name('logout');   
        
        Route::get('/me', [AuthenticationController::class, 'me'])
            ->name('me');
    });
});

Route::prefix('lists')->name('lists.')->middleware('auth')->group(function () {
    Route::post('/status', StatusListController::class)
        ->name('status');
    
    Route::post('/roles', RoleListController::class)
        ->name('roles');

    Route::post('/languages', LanguageListController::class)
        ->name('languages');

    Route::post('/language-levels', LanguageLanguageLevelListController::class)
        ->name('language-levels');

    Route::post('/courses', CourseListController::class)
        ->name('courses');

    Route::post('/sessions', [CalendarController::class, 'calendarSessions'])
        ->name('sessions');

    Route::post('/calendars', [CalendarController::class, 'scheduledCalendarCourses'])
        ->name('calendars');

    Route::post('/ongoing_and_pending_sessions', [CalendarController::class, 'ongoingAndPendingSessions'])
        ->name('ongoing_and_pending_sessions');
});

Route::prefix('settings')->name('settings.')->middleware('auth')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserProfileController::class, 'index'])
            ->name('index')
            ->middleware('can:view users');

        Route::post('/', [UserProfileController::class, 'store'])
            ->name('store')
            ->middleware('can:create users');

        Route::post('profile/{user}/data', [UserProfileController::class, 'userDataResponse'])
            ->name('profile.user.data')
            ->middleware('can:view users');

        Route::post('profile/{user}/edit', [UserProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('profile/{user}/destroy', [UserProfileController::class, 'destroy'])
            ->name('profile.destroy')
            ->middleware('can:delete users');

        Route::post('profile/{idNumber}/profile-data', [UserProfileController::class, 'fetchByIdNumber'])
            ->name('profile.fetchByIdNumber');
    });

    Route::prefix('languages')->name('languages.')->middleware('auth')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])
            ->name('index')
            ->middleware('can:view languages');

        Route::post('/', [LanguageController::class, 'store'])
            ->name('store')
            ->middleware('can:create languages');

        Route::post('/{language}/data', [LanguageController::class, 'languageData'])
            ->name('data')
            ->middleware('can:view languages');

        Route::post('/{language}/edit', [LanguageController::class, 'update'])
            ->name('edit')
            ->middleware('can:edit languages');

        Route::post('/{language}/destroy', [LanguageController::class, 'destroy'])
            ->name('destroy')
            ->middleware('can:delete languages');
    });
});

Route::prefix('academics')->name('academics.')->middleware('auth')->group(function () {
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::prefix('language-levels')->name('language-levels.')->group(function () {
            Route::get('/', [LanguageLevelController::class, 'index'])
                ->name('index')
                ->middleware('can:view language levels');

            Route::post('/', [LanguageLevelController::class, 'store'])
                ->name('store')
                ->middleware('can:create language levels');

            Route::post('/{languageLevel}/data', [LanguageLevelController::class, 'languageLevelData'])
                ->name('data')
                ->middleware('can:view language levels');

            Route::post('/{languageLevel}/edit', [LanguageLevelController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit language levels');

            Route::post('/{languageLevel}/destroy', [LanguageLevelController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete language levels');
        });

        Route::prefix('level-contents')->name('level-contents.')->group(function () {
            Route::get('/', [LevelContentController::class, 'index'])
                ->name('index')
                ->middleware('can:view level contents');

            Route::post('/', [LevelContentController::class, 'store'])
                ->name('store')
                ->middleware('can:create level contents');

            Route::post('/{levelContent}/data', [LevelContentController::class, 'levelContentData'])
                ->name('data')
                ->middleware('can:view level contents');

            Route::post('/{levelContent}/edit', [LevelContentController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit level contents');

            Route::post('/{levelContent}/destroy', [LevelContentController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete level contents');
        });

        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])
                ->name('index')
                ->middleware('can:view courses');

            Route::post('/', [CourseController::class, 'store'])
                ->name('store')
                ->middleware('can:create courses');

            Route::post('/{course}/data', [CourseController::class, 'courseData'])
                ->name('data')
                ->middleware('can:view courses');

            Route::post('/{course}/edit', [CourseController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit courses');

            Route::post('/{course}/destroy', [CourseController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete courses');
        });

        Route::prefix('study-programs')->name('study-programs.')->group(function () {
            Route::get('/', [StudyProgramController::class, 'index'])
                ->name('index')
                ->middleware('can:view study programs');

            Route::post('/', [StudyProgramController::class, 'store'])
                ->name('store')
                ->middleware('can:create study programs');

            Route::post('/{studyProgram}/data', [StudyProgramController::class, 'studyProgramData'])
                ->name('data')
                ->middleware('can:view study programs');

            Route::post('/{studyProgram}/edit', [StudyProgramController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit study programs');

            Route::post('/{studyProgram}/destroy', [StudyProgramController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete study programs');

            Route::prefix('weeks')->name('weeks.')->group(function () {
                Route::post('/study-program/{studyProgram}/data', [StudyProgramWeekController::class, 'createData'])
                    ->name('create-data')
                    ->middleware('can:edit study program week');

                Route::post('/study-program/{studyProgram}', [StudyProgramWeekController::class, 'store'])
                    ->name('store')
                    ->middleware('can:edit study program week');

                Route::post('/{week}/data', [StudyProgramWeekController::class, 'data'])
                    ->name('data')
                    ->middleware('can:edit study program week');

                Route::post('/{week}/edit', [StudyProgramWeekController::class, 'update'])
                    ->name('edit')
                    ->middleware('can:edit study program week');

                Route::post('/{week}/destroy', [StudyProgramWeekController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('can:delete study program week');
            });

            Route::prefix('activities')->name('activities.')->group(function () {
                Route::post('/study-program-week/{week}/data', [StudyProgramWeekActivityController::class, 'createData'])
                    ->name('create-data')
                    ->middleware('can:edit study program week activity');

                Route::post('/study-program-week/{week}', [StudyProgramWeekActivityController::class, 'store'])
                    ->name('store')
                    ->middleware('can:edit study program week activity');

                Route::post('/{activity}/data', [StudyProgramWeekActivityController::class, 'data'])
                    ->name('data')
                    ->middleware('can:edit study program week activity');

                Route::post('/{activity}/edit', [StudyProgramWeekActivityController::class, 'update'])
                    ->name('edit')
                    ->middleware('can:edit study program week activity');

                Route::post('/{activity}/destroy', [StudyProgramWeekActivityController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('can:delete study program week activity');
            });
        });

        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/', [TeacherController::class, 'index'])
                ->name('index')
                ->middleware('can:view teachers');

            Route::post('/', [TeacherController::class, 'store'])
                ->name('store')
                ->middleware('can:create teachers');

            Route::post('/{teacher}/data', [TeacherController::class, 'userData'])
                ->name('data')
                ->middleware('can:view teachers');

            Route::post('/{teacher}/edit', [TeacherController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit teachers');

            Route::post('/{teacher}/destroy', [TeacherController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete teachers');
        });

        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [StudentController::class, 'index'])
                ->name('index')
                ->middleware('can:view students');

            Route::post('/', [StudentController::class, 'store'])
                ->name('store')
                ->middleware('can:create students');

            Route::post('/{student}/data', [StudentController::class, 'userData'])
                ->name('data')
                ->middleware('can:view students');

            Route::post('/{student}/edit', [StudentController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit students');

            Route::post('/{student}/destroy', [StudentController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete students');
        });
    });
    Route::prefix('lessons')->name('lessons.')->group(function () {
        Route::prefix('class-schedules')->name('class-schedules.')->group(function () {
            Route::get('/', [ClassScheduleController::class, 'index'])
                ->name('index')
                ->middleware('can:view class schedules');

            Route::post('/', [ClassScheduleController::class, 'store'])
                ->name('store')
                ->middleware('can:create class schedules');

            Route::post('/{classSchedule}/data', [ClassScheduleController::class, 'classScheduleData'])
                ->name('data')
                ->middleware('can:view class schedules');

            Route::post('/{classSchedule}/edit', [ClassScheduleController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit class schedules');

            Route::post('/{classSchedule}/feedback', [ClassScheduleController::class, 'updateFeedback'])
                ->name('feedback')
                ->middleware('can:view class schedules');

            Route::post('/{classSchedule}/destroy', [ClassScheduleController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete class schedules');

            Route::prefix('details')->name('details.')->group(function () {
                Route::post('/', [ClassScheduleDetailController::class, 'store'])
                    ->name('store')
                    ->middleware('can:create class schedule details');

                Route::post('/{detail}/edit', [ClassScheduleDetailController::class, 'update'])
                    ->name('edit')
                    ->middleware('can:edit class schedule details');

                Route::post('/{detail}/destroy', [ClassScheduleDetailController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('can:delete class schedule details');
            });
        });

        Route::prefix('class-records')->name('class-records.')->group(function () {
            Route::get('/', [ClassRecordController::class, 'index'])
                ->name('index')
                ->middleware('can:view class records');

            Route::post('/class-schedule-details/{detail}/context', [ClassRecordController::class, 'classScheduleDetailContext'])
                ->name('class-schedule-details.context')
                ->middleware('can:view class records');

            Route::post('/form-data', [ClassRecordController::class, 'formData'])
                ->name('form-data')
                ->middleware('can:create class records');

            Route::post('/', [ClassRecordController::class, 'store'])
                ->name('store')
                ->middleware('can:create class records');

            Route::post('/{classRecord}/data', [ClassRecordController::class, 'classRecordData'])
                ->name('data')
                ->middleware('can:view class records');

            Route::post('/{classRecord}/edit', [ClassRecordController::class, 'update'])
                ->name('edit')
                ->middleware('can:edit class records');

            Route::post('/{classRecord}/student-production', [ClassRecordController::class, 'saveStudentProduction'])
                ->name('student-production.save')
                ->middleware('can:view class records');

            Route::post('/{record}/destroy', [ClassRecordController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete class records');

            Route::prefix('details')->name('details.')->group(function () {
                Route::post('/{detail}/data', [ClassRecordController::class, 'detailFormData'])
                    ->name('data')
                    ->middleware('can:edit class records');

                Route::post('/{detail}/edit', [ClassRecordController::class, 'updateDetail'])
                    ->name('edit')
                    ->middleware('can:edit class records');

                Route::post('/{detail}/destroy', [ClassRecordController::class, 'destroyDetail'])
                    ->name('destroy')
                    ->middleware('can:delete class records');
            });
        });

        Route::prefix('distance-activities')->name('distance-activities.')->group(function () {
            Route::get('/', [DistanceActivityController::class, 'index'])
                ->name('index');

            Route::post('/{distanceActivity}/data', [DistanceActivityController::class, 'data'])
                ->name('data');

            Route::post('/details/{detail}/complete', [DistanceActivityController::class, 'completeDetail'])
                ->name('details.complete');

            Route::post('/details/{detail}/video-open', [DistanceActivityController::class, 'recordVideoOpen'])
                ->name('details.video-open');

            Route::post('/details/{detail}/student-production', [DistanceActivityController::class, 'saveStudentProduction'])
                ->name('details.student-production.save');

            Route::post('/detail-students/{detailStudent}/complete', [DistanceActivityController::class, 'updateManagedDetailCompletion'])
                ->name('detail-students.complete');

            Route::post('/detail-students/{detailStudent}/submissions/{media}/destroy', [DistanceActivityController::class, 'deleteStudentSubmission'])
                ->name('detail-students.submissions.destroy');
        });
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::post('/monthly-classes', [MonthlyClassesReportController::class, 'index'])
            ->name('monthly-classes')
            ->middleware('can:view teacher hours report');

        Route::post('/monthly-classes/options/months', [MonthlyClassesReportController::class, 'monthOptions'])
            ->name('monthly-classes.options.months')
            ->middleware('can:view teacher hours report');

        Route::post('/monthly-classes/options/students', [MonthlyClassesReportController::class, 'studentOptions'])
            ->name('monthly-classes.options.students')
            ->middleware('can:view teacher hours report');

        Route::post('/monthly-classes/export/excel', [MonthlyClassesReportController::class, 'exportExcel'])
            ->name('monthly-classes.export.excel')
            ->middleware('can:view teacher hours report');

        Route::post('/monthly-classes/export/pdf', [MonthlyClassesReportController::class, 'exportPdf'])
            ->name('monthly-classes.export.pdf')
            ->middleware('can:view teacher hours report');

        Route::post('/teacher-hours', [TeacherHoursReportController::class, 'index'])
            ->name('teacher-hours')
            ->middleware('can:view teacher hours report');

        Route::post('/teacher-hours/export/excel', [TeacherHoursReportController::class, 'exportExcel'])
            ->name('teacher-hours.export.excel')
            ->middleware('can:view teacher hours report');

        Route::post('/teacher-hours/export/pdf', [TeacherHoursReportController::class, 'exportPdf'])
            ->name('teacher-hours.export.pdf')
            ->middleware('can:view teacher hours report');
    });
});

Route::get('/test', function () {
    return view('test');
});

Route::prefix('email/class-reminder')->name('email.class-reminder.')->group(function () {
    // New single entry point from email "Avisar" button
    Route::get('/notify/{detail}/{student}', [ClassReminderActionController::class, 'showNotifyPage'])
        ->name('notify')
        ->middleware('signed');

    // Legacy routes — keep for old emails still in inboxes
    Route::get('/pending/{detail}/{student}', [ClassReminderActionController::class, 'confirmPending'])
        ->name('pending')
        ->middleware('signed');

    Route::get('/upload-task/{detail}/{student}', [ClassReminderActionController::class, 'confirmUploadTask'])
        ->name('upload-task')
        ->middleware('signed');

    Route::post('/execute/{action}/{detail}/{student}', [ClassReminderActionController::class, 'execute'])
        ->name('execute')
        ->middleware('signed');

    Route::get('/done', [ClassReminderActionController::class, 'showDonePage'])
        ->name('done');
});


Route::get('/{any}', function () {
    return view('app'); // your Vue root template
})->where('any', '.*');
