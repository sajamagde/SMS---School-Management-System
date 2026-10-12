<?php

use App\Http\Controllers\Alerts\StudentAlertController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('teacher/alerts')->group(function () {
        Route::get('/classes', [StudentAlertController::class, 'classes']);
        Route::get('/sections/{sectionId}/students', [StudentAlertController::class, 'students']);
        Route::get('/students/{student}', [StudentAlertController::class, 'showStudent']);
        Route::post('/students/{student}', [StudentAlertController::class, 'store']);
    });
    Route::middleware('parent.child')->group(function () {
        Route::prefix('parent/alerts')->group(function () {
            Route::get('/', [StudentAlertController::class, 'index']);
            Route::get('/{alertId}', [StudentAlertController::class, 'show']);
        });
    });
});
