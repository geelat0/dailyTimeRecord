<?php

use App\DataTables\TimeSheetDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TimeSheetController;
use App\Http\Controllers\LogViewerController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EmpowerExLoginController;


Route::get('/docs/api', [Controller::class, 'show']);

// Log Viewer Routes
Route::get('/logs', [LogViewerController::class, 'index'])->name('logs.viewer');
Route::get('/logs/get', [LogViewerController::class, 'getLogs'])->name('logs.get');
Route::post('/logs/clear', [LogViewerController::class, 'clearLogs'])->name('logs.clear');

Route::get('/{any}', function () {
    return Inertia::render('App');
})->where('any', '.*')->name('app');




