<?php

use App\DataTables\TimeSheetDataTable;
use App\Http\Controllers\ApprovedAttendanceController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\TimeSheetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestStatusController;

Route::middleware('auth:api')->group(function () {

    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::post('/time-entries/time-in', [TimeEntryController::class, 'updateTimeIn']);
    Route::post('/time-entries/time-out', [TimeEntryController::class, 'updateTimeOut']);
    Route::post('/time-entries/time-in-pm', [TimeEntryController::class, 'updateTimeInPM']);
    Route::post('/time-entries/time-out-pm', [TimeEntryController::class, 'updateTimeOutPM']);
    Route::get('/time-entries/get-user-time-entries', [TimeEntryController::class, 'getUserTimeEntries']);
    Route::get('/time-entries/check-shift-schedule', [TimeEntryController::class, 'checkShiftSchedule']);

    Route::get('/time-entries/list/{start_date}/{end_date}', [TimeSheetController::class, 'index']);
    Route::post('/time-entries/update', [TimeSheetController::class, 'updateTimeEntry']);
    Route::get('/time-entries/get-by-id', [TimeSheetController::class, 'getTimeEntryByID']);
    Route::get('/getAttendanceType', [TimeSheetController::class, 'getAttendanceType']);
    Route::get('/compute-total-rendered-hours', [TimeSheetController::class, 'computeRenderedHoursPerCutOff']);

    Route::post('/approved-attendance/store', [ApprovedAttendanceController::class, 'store']);
    Route::get('/attachment-history/list', [ApprovedAttendanceController::class, 'index']);
    Route::get('download-attachment/{filename}', [ApprovedAttendanceController::class, 'download'])->name('attachment.download');

    Route::get('/shift-schedule/list', [SettingsController::class, 'index']);
    Route::get('/getShift', [SettingsController::class, 'getShift']);
    Route::post('/shift-schedule/store', [SettingsController::class, 'store']);
    Route::post('/shift-schedule/update', [SettingsController::class, 'update']);

    Route::get('/download-dtr', [PDFController::class, 'downloadDTR']);
    Route::post('/submit-dtr', [PDFController::class, 'submitDTR'])->name('submit-dtr');
    Route::get('/approvers', [PDFController::class, 'getApprover'])->name('approvers');


    Route::get('/request-status/list', [RequestStatusController::class, 'index']);
    Route::post('/request-status/open-attachment', [RequestStatusController::class, 'openAttachment']);
});

Route::get('download-attachment/{filename}', [ApprovedAttendanceController::class, 'download'])->name('attachment.download');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);






