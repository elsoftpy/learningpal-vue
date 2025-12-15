<?php

use App\Http\Controllers\Academics\Settings\LanguageLevelController;
use App\Http\Controllers\Auth\AuthenticationController;
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
    
    Route::post('/roles/', RoleListController::class)
        ->name('roles');
});

Route::prefix('settings')->name('settings.')->middleware('auth')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserProfileController::class, 'index'])
            ->name('index')
            ->middleware('can:view users');

        Route::post('/', [UserProfileController::class, 'store'])
            ->name('store')
            ->middleware('can:create users');

        Route::post('profile/{user}/data', [UserProfileController::class, 'userData'])
            ->name('profile.user.data')
            ->middleware('can:view users');

        Route::post('profile/{user}/edit', [UserProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('profile/{user}/destroy', [UserProfileController::class, 'destroy'])
            ->name('profile.destroy')
            ->middleware('can:delete users');
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
        Route::get('/language-levels', [LanguageLevelController::class, 'index'])
            ->name('language_levels')
            ->middleware('can:view language levels');

        Route::post('/language-levels', [LanguageLevelController::class, 'store'])
            ->name('language_levels.store')
            ->middleware('can:create language levels');

        Route::post('/language-levels/{languageLevel}/data', [LanguageLevelController::class, 'languageLevelData'])
            ->name('language_levels.data')
            ->middleware('can:view language levels');

        Route::post('/language-levels/{languageLevel}/edit', [LanguageLevelController::class, 'update'])
            ->name('language_levels.edit')
            ->middleware('can:edit language levels');
            
        Route::post('/language-levels/{languageLevel}/destroy', [LanguageLevelController::class, 'destroy'])
            ->name('language_levels.destroy')
            ->middleware('can:delete language levels');
    });
});

Route::get('/test', function () {
    return view('test');
});


Route::get('/{any}', function () {
    return view('app'); // your Vue root template
})->where('any', '.*');
