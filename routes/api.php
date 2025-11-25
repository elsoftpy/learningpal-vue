<?php

use App\Http\Controllers\Api\v1\Auth\ApiAuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {

        Route::post('/login', [ApiAuthenticationController::class, 'login'])
            ->name('login');

        Route::post('/register', [ApiAuthenticationController::class, 'register'])
            ->name('register');
    });
});

Route::get('/authorization-test', function (Request $request) {
    
    return response()->json([
        'success' => true,
        'message' => __('You have access to this restricted resource.'),
        'data' => null,
        'errors' => [],
    ]);
})->middleware(['auth:sanctum', 'can:access-restricted-resource']);
