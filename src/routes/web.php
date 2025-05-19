<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceHistoryController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\AdminAttendanceHistoryController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminCorrectionRequestController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.post');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.post');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//ユーザー
Route::middleware(['auth'])->group(function () {
    Route::group(['middleware' => 'verified'], function() {
        Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
        Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
        Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

        Route::get('/attendance/list', [AttendanceHistoryController::class, 'index'])->name('staff.attendances.index');
        Route::get('/attendance/{id}', [AttendanceHistoryController::class, 'detail'])->name('attendance.detail');
        Route::post('/attendance/{attendance}/correction-request', [AttendanceHistoryController::class, 'storeCorrectionRequest'])->name('attendance.correction.request');

        Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('staff.correction_requests.index');
    });
});

// 管理者
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function() {
        Route::get('attendance/list', [AdminAttendanceHistoryController::class, 'index'])->name('admin.attendances.index');
        Route::get('attendance/{id}', [AdminAttendanceHistoryController::class, 'detail'])->name('admin.attendance.detail');
        Route::post('attendance/{id}/update', [AdminAttendanceHistoryController::class, 'update'])->name('admin.attendance.update');
    
        Route::get('staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::get('attendance/staff/{id}', [AdminStaffController::class, 'detail'])->name('admin.staff.show');
    
        Route::get('stamp_correction_request/list', [AdminCorrectionRequestController::class, 'index'])->name('admin.correction_requests.index');
        Route::get('stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'detail'])->name('admin.correction_requests.show');
        Route::put('correction_requests/{id}/approve', [AdminCorrectionRequestController::class, 'approve'])->name('admin.correction_requests.approve');
        Route::put('correction_requests/{id}/reject', [AdminCorrectionRequestController::class, 'reject'])->name('admin.correction_requests.reject');
    });
});


