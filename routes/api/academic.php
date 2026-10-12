<?php

use App\Http\Controllers\Academic\AcademicDistributionController;
use App\Http\Controllers\Academic\AcademicYearController;
use App\Http\Controllers\Academic\GradeController;
use App\Http\Controllers\Academic\SectionController;
use App\Http\Controllers\Academic\SubjectController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('academic-years')->group(function () {

        Route::get('/', [
            AcademicYearController::class,
            'index'
        ]);

        Route::get('/active', [
            AcademicYearController::class,
            'active'
        ]);

        Route::get('/current-term', [
            AcademicYearController::class,
            'currentTerm'
        ]);

        Route::post('/', [
            AcademicYearController::class,
            'store'
        ]);

        Route::patch('/{id}', [
            AcademicYearController::class,
            'update'
        ]);

        Route::post('/{id}/activate', [
            AcademicYearController::class,
            'activate'
        ]);

        Route::post('/{id}/deactivate', [
            AcademicYearController::class,
            'deactivate'
        ]);

        Route::delete('/{id}', [
            AcademicYearController::class,
            'destroy'
        ]);
    });
});
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('academic')->group(function () {

        Route::get('/subjects', [
            SubjectController::class,
            'getSubjects'
        ]);
        Route::post('/subjects', [SubjectController::class, 'createOrAttach']);

        Route::get('/grades', [
            GradeController::class,
            'getGrades'
        ]);

        Route::get('/sections', [
            SectionController::class,
            'getSectionsByGrade'
        ]);
        Route::post(
            '/distribution/auto',
            [AcademicDistributionController::class, 'autoDistribute']
        );
        Route::get(
            '/distribution/students',
            [AcademicDistributionController::class, 'getStudents']
        );

        Route::patch(
            '/distribution/students/{studentId}/section',
            [AcademicDistributionController::class, 'moveStudent']
        );
        Route::patch(
            '/sections/{sectionId}/status',
            [SectionController::class, 'updateStatus']
        );
    });
});
