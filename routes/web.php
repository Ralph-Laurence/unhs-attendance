<?php

use App\Http\Controllers\backoffice\AbsenceController;
use App\Http\Controllers\backoffice\AttendanceController;
use App\Http\Controllers\backoffice\AuditTrailsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\backoffice\DailyTimeRecordController;
use App\Http\Controllers\backoffice\DashboardController;
use App\Http\Controllers\backoffice\EmployeeControllerBase;
use App\Http\Controllers\backoffice\GenericEmployeeController;
use App\Http\Controllers\backoffice\TeachersController;
use App\Http\Controllers\backoffice\StaffController;
use App\Http\Controllers\backoffice\LateAttendanceController;
use App\Http\Controllers\backoffice\LeaveRequestsController;
use App\Http\Controllers\scanner\ScannerController;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\Faculty;
use App\Models\Staff;
use Hashids\Hashids;
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

Route::controller(DashboardController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/dashboard', 'index')->name(RouteNames::Dashboard['index']);
});

Route::controller(DailyTimeRecordController::class)->middleware(['auth'])
->group(function()
{
    Route::post('/backoffice/dtr',             'index')          ->name(RouteNames::DailyTimeRecord['index']);
    Route::post('/backoffice/dtr/timerecords', 'getTimeRecords') ->name(RouteNames::DailyTimeRecord['get']);
    Route::post('/backoffice/dtr/export/pdf',  'exportPdf')      ->name(RouteNames::DailyTimeRecord['exportPdf']);
});

Route::controller(ScannerController::class)->group(function()
{
    Route::get('/dtr-scanner', 'index')->name(RouteNames::Scanner['index']);

    // AJAX requests
    Route::get('/dtr-scanner/history',   'history')         ->name(RouteNames::Scanner['history']);
    Route::post('/dtr-scanner/decode/',  'decode')          ->name(RouteNames::Scanner['decode']);
    Route::post('/dtr-scanner/pin/auth', 'authenticatePin') ->name(RouteNames::Scanner['authpin']);
});

Route::controller(AttendanceController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/attendance', 'index')->name(RouteNames::Attendance['index']);

    // This will be exectued by CRON JOB
    Route::get('/backoffice/attendance/auto-absent', 'autoAbsentEmployees')->name(RouteNames::Attendance['autoAbsent']);

    Route::post('/backoffice/attendance/get',        'getAttendances')->name(RouteNames::Attendance['get']);
    Route::post('/backoffice/attendance/delete',     'destroy')       ->name(RouteNames::Attendance['delete']);
});

Route::controller(AbsenceController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/attendance/absence',         'index')       ->name(RouteNames::Absence['index']);

    Route::post('/backoffice/attendance/absence/get',    'getAbsences') ->name(RouteNames::Absence['get']);
    Route::post('/backoffice/attendance/absence/delete', 'destroy')     ->name(RouteNames::Absence['delete']);
});

Route::controller(LateAttendanceController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/attendance/late',         'index')      ->name(RouteNames::Late['index']);

    Route::post('/backoffice/attendance/late/get',    'getRecords') ->name(RouteNames::Late['get']);
    Route::post('/backoffice/attendance/late/delete', 'destroy')    ->name(RouteNames::Late['delete']);
});

Route::controller(LeaveRequestsController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/leave', 'index')->name(RouteNames::Leave['index']);

    Route::post('/backoffice/leave/get'     , 'getRecords')   ->name(RouteNames::Leave['get']);
    Route::post('/backoffice/leave/create'  , 'store')        ->name(RouteNames::Leave['create']);
    Route::post('/backoffice/leave/delete'  , 'destroy')      ->name(RouteNames::Leave['delete']);
    Route::post('/backoffice/leave/edit'    , 'edit')         ->name(RouteNames::Leave['edit']);
    Route::post('/backoffice/leave/approve' , 'approveLeave') ->name(RouteNames::Leave['approve']);
    Route::post('/backoffice/leave/reject'  , 'rejectLeave')  ->name(RouteNames::Leave['reject']);
});

// Route::get('/home', function() {
//     return view('home');
// })->middleware(['auth']);


Route::controller(GenericEmployeeController::class)->middleware(['auth'])
->group(function()
{
    Route::post('/xhr/employees/list/empno',  'loadEmpNumbers')->name(RouteNames::Employee['list-empno']);
    Route::post('/xhr/employees/send/qrcode', 'resendQRCode')  ->name(RouteNames::Employee['resendqr']);

});

Route::controller(TeachersController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/employees/faculty',           'index')        ->name(RouteNames::Faculty['index']);
    
    Route::post('/backoffice/employees/faculty/get',      'getTeachers')  ->name(RouteNames::Faculty['all']);
    Route::post('/backoffice/employees/faculty/create',   'store')        ->name(RouteNames::Faculty['create']);
    Route::post('/backoffice/employees/faculty/delete',   'destroy')      ->name(RouteNames::Faculty['destroy']);
    Route::post('/backoffice/employees/faculty/details',  'show')         ->name(RouteNames::Faculty['show']);
    Route::post('/backoffice/employees/faculty/update',   'update')       ->name(RouteNames::Faculty['update']);
    Route::post('/backoffice/employees/faculty/edit',     'edit')         ->name(RouteNames::Faculty['edit']);
});

Route::controller(StaffController::class)->middleware(['auth'])
->group(function()
{
    Route::get('/backoffice/employees/staff',           'index')        ->name(RouteNames::Staff['index']);
    
    Route::post('/backoffice/employees/staff/get',      'getStaff')     ->name(RouteNames::Staff['all']);
    Route::post('/backoffice/employees/staff/create',   'store')        ->name(RouteNames::Staff['create']);
    Route::post('/backoffice/employees/staff/delete',   'destroy')      ->name(RouteNames::Staff['destroy']);
    Route::post('/backoffice/employees/staff/details',  'show')         ->name(RouteNames::Staff['show']);
    Route::post('/backoffice/employees/staff/update',   'update')       ->name(RouteNames::Staff['update']);
    Route::post('/backoffice/employees/staff/edit',     'edit')         ->name(RouteNames::Staff['edit']);
});

Route::controller(AuditTrailsController::class)->middleware(['auth']) //['only_su']
->group(function()
{
    Route::get('/backoffice/audit-trails',  'index')->name(RouteNames::AuditTrails['index']);
    Route::post('/backoffice/audit-trails', 'getAll')->name(RouteNames::AuditTrails['all']);
});

Route::controller(TestController::class)->group(function(){
    Route::get('/test',     'index');
    Route::get('/qrtest',   'qrsamples');
    Route::get('/pintest',  'pinsamples');
});

Route::get('/download/qr-code/{filename}', function($filename)
{
    // Define the file path
    $path = Extensions::getQRCode_storagePath($filename);

    // Download the file
    $file = response()->download($path, $filename);

    // Delete the file after download
    $file->deleteFileAfterSend(true);

    return $file;

})->name('qr-download')->middleware(['auth']);

Route::get('/testscan', function() {
    $hash = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    $encode = '0GAqKE3Wwz';
    $decode = $hash->decode($encode)[0];

    return "decoded -> $decode";
});

Route::get('/uri', function()
{
    $models = [
        Employee::class,
        Staff::class,
        Faculty::class
    ];

    return dump(print_r($models, true));
});