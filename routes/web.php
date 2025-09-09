<?php

use App\Http\Controllers\AttendanceController as UserAttendanceController;
use App\Http\Controllers\AttendanceRequestController as UserAttendanceRequestController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

// ユーザー認証
Route::middleware(['auth:web'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance',  [UserAttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/checkout', [UserAttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/attendance/list',  [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/{id}/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/attendance/{id}/break/end', [BreakController::class, 'end'])->name('break.end');
    Route::get('/attendance/detail/{id}', [UserAttendanceRequestController::class, 'create'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [UserAttendanceRequestController::class, 'store'])->name('attendance_request.store');
    Route::get('/stamp_correction_request/{id}', [UserAttendanceRequestController::class, 'show'])->name('attendance_requests.show');
});

// 管理者認証
Route::prefix('admin')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('admin.loginForm');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login');
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index']);
    });
});