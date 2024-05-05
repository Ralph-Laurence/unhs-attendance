<?php

use App\Http\Controllers\backoffice\AbsenceController;
use App\Http\Controllers\backoffice\AttendanceController;
use App\Http\Controllers\backoffice\AuditTrailsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\backoffice\DailyTimeRecordController;
use App\Http\Controllers\backoffice\DashboardController;
use App\Http\Controllers\backoffice\GenericEmployeeController;
use App\Http\Controllers\backoffice\TeachersController;
use App\Http\Controllers\backoffice\StaffController;
use App\Http\Controllers\backoffice\LateAttendanceController;
use App\Http\Controllers\backoffice\LeaveRequestsController;
use App\Http\Controllers\backoffice\SecurityGuardController;
use App\Http\Controllers\scanner\ScannerController;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function() 
{
    Route::controller(DashboardController::class)
    ->group(function()
    {
        //Route::get('/',     'index')->name(RouteNames::Dashboard['root']);
        Route::get('/home', 'index')->name(RouteNames::Dashboard['home']);
        Route::get('/backoffice/dashboard', 'index')->name(RouteNames::Dashboard['index']);
    
        Route::post('/backoffice/dashboard/attendance/stats',     'findAttxStatistics')->name(RouteNames::Dashboard['attendanceStats']);
        Route::post('/backoffice/dashboard/leavereqts/stats',     'findLeaveStatistics')->name(RouteNames::Dashboard['leavereqtsStats']);
    
        Route::post('/backoffice/dashboard/graphings/employee',   'getEmpGraphings')->name(RouteNames::Dashboard['countEmp']);
        Route::post('/backoffice/dashboard/graphings/attendance', 'getAttendanceGraphings')->name(RouteNames::Dashboard['countAttendance']);
        Route::post(
            '/backoffice/dashboard/graphings/attendance/find/monthly', 
            'findMonthlyAttendance'
        )->name(RouteNames::Dashboard['findMonthly']);

        Route::post('/backoffice/dashboard/stats/employees', 'findEmpStatus')->name(RouteNames::Dashboard['empStats']);
    });
    
    Route::controller(DailyTimeRecordController::class)
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
    
    Route::controller(AttendanceController::class)
    ->group(function()
    {
        Route::get('/backoffice/attendance', 'index')->name(RouteNames::Attendance['index']);
    
        // This will be exectued by WINDOWS TASK SCHEDULER
        Route::get('/backoffice/attendance/auto-absent', 'autoAbsentEmployees')->name(RouteNames::Attendance['autoAbsent']);
    
        // This will be exectued by CRON JOB .ORG
        //Route::get('/backoffice/cron/auto-absent', 'autoAbsentEmployees')->name('cron.autoAbsent');
    
        Route::post('/backoffice/attendance/get',        'getAttendances')->name(RouteNames::Attendance['get']);
        Route::post('/backoffice/attendance/delete',     'destroy')       ->name(RouteNames::Attendance['delete']);
    });
    
    Route::controller(AbsenceController::class)
    ->group(function()
    {
        Route::get('/backoffice/attendance/absence',         'index')       ->name(RouteNames::Absence['index']);
    
        Route::post('/backoffice/attendance/absence/get',    'getAbsences') ->name(RouteNames::Absence['get']);
        Route::post('/backoffice/attendance/absence/delete', 'destroy')     ->name(RouteNames::Absence['delete']);
    });
    
    Route::controller(LateAttendanceController::class)
    ->group(function()
    {
        Route::get('/backoffice/attendance/late',         'index')      ->name(RouteNames::Late['index']);
    
        Route::post('/backoffice/attendance/late/get',    'getRecords') ->name(RouteNames::Late['get']);
        Route::post('/backoffice/attendance/late/delete', 'destroy')    ->name(RouteNames::Late['delete']);
    });
    
    Route::controller(LeaveRequestsController::class)
    ->group(function()
    {
        Route::get('/backoffice/leave', 'index')->name(RouteNames::Leave['index']);
    
        Route::post('/backoffice/leave/get'     , 'getRecords')   ->name(RouteNames::Leave['get']);
        Route::post('/backoffice/leave/create'  , 'store')        ->name(RouteNames::Leave['create']);
        Route::post('/backoffice/leave/delete'  , 'destroy')      ->name(RouteNames::Leave['delete']);
        Route::post('/backoffice/leave/edit'    , 'edit')         ->name(RouteNames::Leave['edit']);
        Route::post('/backoffice/leave/approve' , 'approveLeave') ->name(RouteNames::Leave['approve']);
        Route::post('/backoffice/leave/reject'  , 'rejectLeave')  ->name(RouteNames::Leave['reject']);
    
        // This will be executed by CRON JOB .ORG
        //Route::get('/backoffice/cron/leave', 'autoUpdateEmployeeLeaveStatus')->name('cron.autoUpdateLeave');
    });
    
    
    Route::controller(GenericEmployeeController::class)
    ->group(function()
    {
        Route::post('/xhr/employees/list/empno',  'loadEmpNumbers') ->name(RouteNames::Employee['list-empno']);
        Route::post('/xhr/employees/send/qrcode', 'resendQRCode')   ->name(RouteNames::Employee['resendqr']);
        Route::post('/xhr/employees/list/empnos', 'listEmployeeNos')->name(RouteNames::Employee['get-empnos']);
    });
    
    Route::controller(TeachersController::class)
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
    
    Route::controller(StaffController::class)
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
    
    Route::controller(SecurityGuardController::class)
    ->group(function()
    {
        Route::get('/backoffice/employees/guard',           'index')        ->name(RouteNames::Guards['index']);
        
        Route::post('/backoffice/employees/guard/get',      'getGuards')    ->name(RouteNames::Guards['all']);
        Route::post('/backoffice/employees/guard/create',   'store')        ->name(RouteNames::Guards['create']);
        Route::post('/backoffice/employees/guard/delete',   'destroy')      ->name(RouteNames::Guards['destroy']);
        Route::post('/backoffice/employees/guard/details',  'show')         ->name(RouteNames::Guards['show']);
        Route::post('/backoffice/employees/guard/update',   'update')       ->name(RouteNames::Guards['update']);
        Route::post('/backoffice/employees/guard/edit',     'edit')         ->name(RouteNames::Guards['edit']);
    });
    
    Route::controller(AuditTrailsController::class) //['only_su']
    ->group(function()
    {
        Route::get('/backoffice/audit-trails',  'index')->name(RouteNames::AuditTrails['index']);
    
        Route::post('/backoffice/audit-trails/all'      , 'getAll')->name(RouteNames::AuditTrails['all']);
        Route::post('/backoffice/audit-trails/view'     , 'show')->name(RouteNames::AuditTrails['show']);
    
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
    
    })->name('qr-download');
    
});