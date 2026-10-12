<?php

use App\Http\Controllers\Schedules\ClassScheduleController;
use App\Http\Controllers\Schedules\ExamScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('schedules')->group(function () {

        Route::get(
            '/sections/{sectionId}',
            [ClassScheduleController::class, 'sectionIndex']
        );
        Route::get('/class/section/{sectionId}', [
            ClassScheduleController::class,
            'weeklyIndex'
        ]);

        Route::put('/class/section/{sectionId}', [
            ClassScheduleController::class,
            'saveWeeklySchedule'
        ]);

        Route::get('/class/teachers/{sectionId}', [
            ClassScheduleController::class,
            'getTeachersBySection'
        ]);


        Route::get('/class/student', [
            ClassScheduleController::class,
            'studentIndex'
        ]);


        Route::get('/class/teacher', [
            ClassScheduleController::class,
            'teacherIndex'
        ]);

        Route::get('/class/parent', [
            ClassScheduleController::class,
            'parentIndex'
        ])->middleware('parent.child');


        Route::get(
            '/sections/{section}/teacher-availabilities',
            [ClassScheduleController::class, 'getSectionTeacherAvailabilities']
        );

        Route::post(
            '/class/availabilities',
            [ClassScheduleController::class, 'updateAvailability']
        );
        Route::get('/admin/schedule-times',[ClassScheduleController::class, 'scheduleTimes']);
    });

    Route::prefix('exams')->group(function () {
        Route::post('/', [ExamScheduleController::class, 'store']);
        Route::put('/{id}', [ExamScheduleController::class, 'update']);
        Route::delete('/{id}', [ExamScheduleController::class, 'destroy']);

        Route::get('/student', [ExamScheduleController::class, 'studentExams']);
        Route::get('/parent', [ExamScheduleController::class, 'parentExams'])->middleware('parent.child');;
    });
});
