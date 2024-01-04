<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\backoffice\DailyTimeRecordController;
use App\Http\Controllers\scanner\ScannerController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\TestController;
use App\Http\Utils\RouteNames;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Route::controller(DailyTimeRecordController::class)->group(function()
{
    Route::get('/backoffice/daily-time-record', 'index')->name(RouteNames::DailyTimeRecord['index']);
});

Route::controller(ScannerController::class)->group(function()
{
    Route::get('/dtr-scanner',          'index')->name(RouteNames::Scanner['index']);

    // AJAX requests
    Route::get('/dtr-scanner/history',  'history')->name(RouteNames::Scanner['history']);
    Route::post('/dtr-scanner/decode/', 'decode')->name(RouteNames::Scanner['decode']);
});

Route::controller(AttendanceController::class)->group(function()
{
    Route::get('/backoffice/attendance',             'index')->name(RouteNames::Attendance['index']);
    Route::get('/backoffice/attendance/auto-absent', 'autoAbsentEmployees')->name(RouteNames::Attendance['autoAbsent']);

    Route::post('/backoffice/attendance/this-week',  'getWeeklyAttendances')->name(RouteNames::Attendance['weekly']);
    Route::post('/backoffice/attendance/delete',     'destroy')->name(RouteNames::Attendance['delete']);

    Route::post('/backoffice/attendance/daily/{employeeFilter?}', 'getDailyAttendances')->name(RouteNames::Attendance['daily']);
});

Route::controller(TeachersController::class)->group(function()
{
    Route::get('/backoffice/employees/teachers', 'index')->name(RouteNames::Teachers['index']);
    Route::post('/backoffice/employees/teachers/get', 'getTeachers')->name(RouteNames::Teachers['all']);
});

Route::controller(TestController::class)->group(function(){
    Route::get('/test', 'index');
});