<?php

use App\Http\Controllers\Mobile\Auth\MobileAuthController;
use App\Http\Controllers\Mobile\Parent\ParentController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/login', [MobileAuthController::class, 'login']);


});
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/parent/children', [ParentController::class, 'getMyChildren']);
    Route::get('/parents/search', [ParentController::class, 'search']);

});