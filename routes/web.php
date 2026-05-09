<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;

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

Route::get('/', function () {
    return redirect()->route('departments.index');
});

Route::resource('departments', DepartmentController::class)->except(['create', 'edit']);
Route::resource('employees', EmployeeController::class)->except(['create', 'edit']);

Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
Route::get('/attendances/import', [AttendanceController::class, 'importForm'])->name('attendances.import');
Route::get('/attendances/template/download', [AttendanceController::class, 'downloadTemplate'])->name('attendances.template.download');
Route::post('/attendances/preview', [AttendanceController::class, 'preview'])->name('attendances.preview');
Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
