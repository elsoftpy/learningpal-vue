<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Selectable\RoleListController;
use App\Http\Controllers\Selectable\StatusListController;
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

Route::get('/test', function () {
    return view('test');
});


Route::get('/{any}', function () {
    return view('app'); // your Vue root template
})->where('any', '.*');
