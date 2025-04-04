<?php

use App\DataTables\TimeSheetDataTable;
use App\Http\Controllers\TimeSheetController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EmpowerExLoginController;



Route::get('/{any}', function () {
    return Inertia::render('App');
})->where('any', '.*')->name('app');




