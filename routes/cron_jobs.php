<?php

use App\Http\Controllers\backoffice\AttendanceController;
use App\Http\Controllers\backoffice\LeaveRequestsController;
use Illuminate\Support\Facades\Route;

Route::controller(LeaveRequestsController::class)->group(function () 
{
    // This will be executed by CRON JOB .ORG
    Route::get('/backoffice/cron/leave', 'autoUpdateEmployeeLeaveStatus')->name('cron.autoUpdateLeave');
});

Route::controller(AttendanceController::class)->group(function()
{
    // This will be exectued by CRON JOB .ORG
    Route::get('/backoffice/cron/auto-absent', 'autoAbsentEmployees')->name('cron.autoAbsent');
});