<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Grades\GradeController;
use App\Http\Controllers\Grades\ResultController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/supervisor/grades',
        [GradeController::class, 'index']
    );
    Route::put(
        '/supervisor/grades',
        [GradeController::class, 'update']
    );

    Route::get(
        '/student/results',
        [ResultController::class, 'indexStudent']
    );

    Route::get(
        'parent/results',
        [ResultController::class, 'indexParent']
    );
});
