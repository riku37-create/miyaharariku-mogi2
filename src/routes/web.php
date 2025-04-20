<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\AdminAttendanceHistoryController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/attendances', [AttendanceHistoryController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [AttendanceHistoryController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/{attendance}/correction-request', [AttendanceHistoryController::class, 'storeCorrectionRequest'])->name('attendance.correction.request');
});

// 管理者
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceHistoryController::class, 'index'])->name('admin.attendances.index');
});

Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->name('correction_request.list');
