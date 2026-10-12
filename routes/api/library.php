<?php

use App\Http\Controllers\Library\LibraryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('library')
    ->group(function () {

        Route::get('/sections', [
            LibraryController::class,
            'getClasses',
        ])->name('library.sections.index');

        Route::get('/sections/{section}/students', [
            LibraryController::class,
            'getSectionStudents',
        ])->name('library.sections.students');

        Route::get('/sections/{section}/distribution', [
            LibraryController::class,
            'getDistribution',
        ])->name('library.distribution.show');

        Route::put('/sections/{section}/distribution', [
            LibraryController::class,
            'saveDistribution',
        ])->name('library.distribution.update');

        Route::get('/sections/{section}/returns', [
            LibraryController::class,
            'getReturns',
        ])->name('library.returns.show');

        Route::put('/sections/{section}/returns', [
            LibraryController::class,
            'saveReturns',
        ])->name('library.returns.update');
    });