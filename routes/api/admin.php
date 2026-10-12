<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Accounts\UserController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::post('/users', [UserController::class, 'store']);
        Route::post('/logoutFromAllDevices', [AdminAuthController::class, 'logoutFromAllDevices']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        );
    });
});
