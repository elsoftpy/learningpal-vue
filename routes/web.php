<?php

use App\Http\Controllers\Academics\Lessons\CalendarController;
use App\Http\Controllers\Academics\Lessons\ClassScheduleController;
use App\Http\Controllers\Academics\Lessons\ClassScheduleDetailController;
use App\Http\Controllers\Academics\Settings\CourseController;
use App\Http\Controllers\Academics\Settings\LanguageLevelController;
use App\Http\Controllers\Academics\Settings\StudentController;
use App\Http\Controllers\Academics\Settings\TeacherController;
use App\Http\Controllers\Auth\AuthenticationController;
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

    Route::post('/ongoing_sessions', [CalendarController::class, 'ongoingSessions'])
        ->name('ongoing_sessions');
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
    });
});

Route::get('/test', function () {
    return view('test');
});


Route::get('/{any}', function () {
    return view('app'); // your Vue root template
})->where('any', '.*');
