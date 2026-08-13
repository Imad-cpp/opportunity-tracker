<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health/live', static fn (): array => ['status' => 'ok']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', RegisterController::class)->middleware('throttle:5,1');
        Route::post('/login', LoginController::class);
    });

    Route::middleware('auth:web')->group(function (): void {
        Route::post('/auth/logout', LogoutController::class);
        Route::get('/me', MeController::class);
    });
});
