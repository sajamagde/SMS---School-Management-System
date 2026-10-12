<?php

use App\Http\Controllers\Announcements\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'parent.child'])->group(function () {
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::post('/', [AnnouncementController::class, 'store']);
        Route::patch('/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::patch('/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin']);
    });
});
