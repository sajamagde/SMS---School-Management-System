<?php

use App\Http\Controllers\Attendance\AttendanceWeekController;
use App\Http\Controllers\Attendance\ParentJustificationController;
use App\Http\Controllers\Attendance\SupervisorAttendanceController;
use App\Http\Controllers\Mobile\Parent\ParentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('teacher')->group(function () {
        Route::get(
            '/attendance/summary',
            [
                SupervisorAttendanceController::class,
                'summaryTeacher'
            ]
        );


        Route::get(
            '/attendance/history',
            [
                SupervisorAttendanceController::class,
                'historyTeacher'
            ]
        );
    });
    Route::get('/attendance/weeks', [AttendanceWeekController::class, 'index']);
    Route::prefix('student')->group(function () {
        Route::get(
            '/attendance',
            [ParentJustificationController::class, 'getMyAttendance']
        );
        Route::get(
            '/attendance/history',
            [ParentJustificationController::class, 'attendanceHistory']
        );
    });
    Route::prefix('parent')->group(function () {
        Route::post(
            '/justifications',
            [ParentJustificationController::class, 'store']
        );
        Route::get(
            '/justifications',
            [ParentJustificationController::class, 'index']
        )->middleware('parent.child');
        Route::get(
            '/justifications/{justificationId}',
            [ParentJustificationController::class, 'show']
        );
        Route::delete(
            '/justifications/{justificationId}',
            [ParentJustificationController::class, 'destroy']
        );
        Route::get(
            '/students/{studentId}/attendance',
            [ParentJustificationController::class, 'getStudentAttendance']
        );
        Route::get(
            '/justifications/{justificationId}/attachment',
            [ParentJustificationController::class, 'attachment']
        );
        Route::get(
            '/attendance/history',
            [ParentJustificationController::class, 'childAttendanceHistory']
        )->middleware('parent.child');
    });
    Route::prefix('attendance')->group(function () {

        Route::prefix('supervisor')->group(function () {
            Route::get('/sections', [SupervisorAttendanceController::class, 'sections']);
            Route::get(
                '/sections/{sectionId}/students',
                [SupervisorAttendanceController::class, 'students']
            );
            Route::get(
                '/sections/{sectionId}/students/attendance',
                [SupervisorAttendanceController::class, 'studentAttendance']
            );
            Route::post(
                '/sections/{sectionId}/students/attendance',
                [SupervisorAttendanceController::class, 'storeStudentAttendance']
            );
            Route::get(
                '/teachers',
                [SupervisorAttendanceController::class, 'teachers']
            );
            Route::post(
                '/teachers/attendance',
                [SupervisorAttendanceController::class, 'storeTeacherAttendance']
            );
            Route::get(
                '/teachers/{teacherId}',
                [SupervisorAttendanceController::class, 'teacherDetails']
            );
            Route::get(
                '/students/{studentId}',
                [SupervisorAttendanceController::class, 'studentDetails']
            );
            Route::get(
                '/justifications',
                [SupervisorAttendanceController::class, 'justifications']
            );
            Route::get(
                '/justifications/{justificationId}',
                [SupervisorAttendanceController::class, 'justificationDetails']
            );
            Route::post(
                '/justifications/{justificationId}/approve',
                [SupervisorAttendanceController::class, 'approveJustification']
            );

            Route::post(
                '/justifications/{justificationId}/reject',
                [SupervisorAttendanceController::class, 'rejectJustification']
            );
            Route::get(
                'justifications/{justificationId}/attachment',
                [SupervisorAttendanceController::class, 'attachment']
            );
        });
    });
});
