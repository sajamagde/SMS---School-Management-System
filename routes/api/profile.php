<?php

use App\Http\Controllers\Profiles\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/supervisor/profile',
        [ProfileController::class, 'supervisor']
    );
    Route::get(
        '/teacher/profile',
        [ProfileController::class, 'teacher']
    );
    Route::get(
        '/student/profile',
        [ProfileController::class, 'student']
    );
    Route::get(
        '/parent/profile',
        [ProfileController::class, 'parent']
    );
    Route::get(
        '/librarian/profile',
        [ProfileController::class, 'librarian']
    );
});
