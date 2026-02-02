<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');