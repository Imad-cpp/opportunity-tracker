<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\OpportunityController;
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
        Route::get('/dashboard/summary', DashboardController::class);

        Route::get('/opportunities', [OpportunityController::class, 'index']);
        Route::post('/opportunities', [OpportunityController::class, 'store']);
        Route::get('/opportunities/{id}', [OpportunityController::class, 'show']);
        Route::patch('/opportunities/{id}', [OpportunityController::class, 'update']);
        Route::delete('/opportunities/{id}', [OpportunityController::class, 'destroy']);
        Route::post('/opportunities/{id}/status', [OpportunityController::class, 'updateStatus']);
        Route::post('/opportunities/{id}/archive', [OpportunityController::class, 'archive']);
        Route::post('/opportunities/{id}/restore', [OpportunityController::class, 'restore']);
        Route::get('/opportunities/{id}/events', [OpportunityController::class, 'events']);
    });
});
