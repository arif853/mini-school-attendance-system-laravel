<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
	Route::post('login', [AuthController::class, 'login']);
	Route::middleware('auth:sanctum')->group(function () {
		Route::get('me', [AuthController::class, 'me']);
		Route::post('logout', [AuthController::class, 'logout']);
	});
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('students', StudentController::class);

    Route::get('attendance', [AttendanceController::class, 'index']);
	Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore']);
	Route::get('attendance/reports/monthly', [AttendanceController::class, 'monthlyReport']);
	Route::get('attendance/stats/today', [AttendanceController::class, 'todayStats']);
});
