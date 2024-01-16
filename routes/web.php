<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\backoffice\DailyTimeRecordController;
use App\Http\Controllers\backoffice\TeachersController;
use App\Http\Controllers\backoffice\StaffController;
use App\Http\Controllers\scanner\ScannerController;
use App\Http\Utils\Extensions;
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

Route::controller(DailyTimeRecordController::class)->middleware(['auth'])
->group(function()
{
    Route::post('/backoffice/dtr', 'index')->name(RouteNames::DailyTimeRecord['index']);
    Route::post('/backoffice/dtr/timerecords', 'getTimeRecords')->name(RouteNames::DailyTimeRecord['get']);
    Route::post('/backoffice/dtr/export/pdf',  'exportPdf')->name(RouteNames::DailyTimeRecord['exportPdf']);
});

Route::controller(ScannerController::class) //->middleware(['auth'])
->group(function()
{
    Route::get('/dtr-scanner',          'index')->name(RouteNames::Scanner['index']);

    // AJAX requests
    Route::get('/dtr-scanner/history',  'history')->name(RouteNames::Scanner['history']);
    Route::post('/dtr-scanner/decode/', 'decode')->name(RouteNames::Scanner['decode']);
});

Route::controller(AttendanceController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/attendance',             'index')->name(RouteNames::Attendance['index']);

    // This will be exectued by CRON JOB
    Route::get('/backoffice/attendance/auto-absent', 'autoAbsentEmployees')->name(RouteNames::Attendance['autoAbsent']);

    Route::post('/backoffice/attendance/get',        'getAttendances')->name(RouteNames::Attendance['get']);

    //Route::post('/backoffice/attendance/this-week',  'getWeeklyAttendances')->name(RouteNames::Attendance['weekly']);
    //Route::post('/backoffice/attendance/by-month',   'getMonthlyAttendances')->name(RouteNames::Attendance['month']);
    Route::post('/backoffice/attendance/delete',     'destroy')->name(RouteNames::Attendance['delete']);

    //Route::post('/backoffice/attendance/daily/{employeeFilter?}', 'getDailyAttendances')->name(RouteNames::Attendance['daily']);
});

// Route::get('/home', function() {
//     return view('home');
// })->middleware(['auth']);


Route::controller(TeachersController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/employees/teachers',            'index')->name(RouteNames::Teachers['index']);
    
    // AJAX
    Route::post('/backoffice/employees/teachers/get',       'getTeachers')->name(RouteNames::Teachers['all']);
    Route::post('/backoffice/employees/teachers/create',    'store')->name(RouteNames::Teachers['create']);
    Route::post('/backoffice/employees/teachers/delete',    'destroy')->name(RouteNames::Teachers['destroy']);
    Route::post('/backoffice/employees/teachers/details',   'details')->name(RouteNames::Teachers['details']);
    Route::post('/backoffice/employees/teachers/update',    'update')->name(RouteNames::Teachers['update']);
});

Route::controller(StaffController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/employees/staff',           'index')->name(RouteNames::Staff['index']);

    // AJAX
    Route::post('/backoffice/employees/staff/get',      'getStaff')->name(RouteNames::Staff['all']);
    Route::post('/backoffice/employees/staff/create',   'store')->name(RouteNames::Staff['create']);
    Route::post('/backoffice/employees/staff/delete',   'destroy')->name(RouteNames::Staff['destroy']);
    Route::post('/backoffice/employees/staff/details',  'details')->name(RouteNames::Staff['details']);
    Route::post('/backoffice/employees/staff/update',   'update')->name(RouteNames::Staff['update']);
});

Route::controller(TestController::class)->group(function(){
    Route::get('/test',     'index');
    Route::get('/qrtest',   'qrsamples');
});

Route::get('/download/qr-code/{filename}/{outputname?}', function($filename)
{
    // Define the file path
    $path = Extensions::getQRCode_storagePath($filename);

    // Download the file
    $file = response()->download($path, 'qr-code.png');

    // Delete the file after download
    //$file->deleteFileAfterSend(true);

    return $file;

})->name('qr-download')->middleware(['auth']);