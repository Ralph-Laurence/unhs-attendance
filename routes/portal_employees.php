<?php

use App\Http\Controllers\portal\EmployeeAttendanceController;
use App\Http\Controllers\portal\EmployeeHomeController;
use App\Http\Controllers\portal\EmployeeLeaveController;
use App\Http\Controllers\portal\EmployeeLoginController;
use App\Http\Utils\PortalRouteNames;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:employee')
->prefix( PortalRouteNames::Employee_Route_Prefix )
->group(function()
{
    Route::controller(EmployeeLoginController::class)->group(function() 
    {
        Route::get("/logout", 'logout')->name( PortalRouteNames::Employee_Logout );
    });

    Route::controller(EmployeeAttendanceController::class)->group(function()
    {
        Route::get("/attendance",      'index')->name(PortalRouteNames::Employee_Attendance);
        Route::post("/attendance/all", 'getAttendances')->name(PortalRouteNames::Employee_Attendance_Xhr_Get);
    });

    // Route::controller(EmployeeHomeController::class)->group(function()
    // {
    //     Route::get("/home",       'index')->name( PortalRouteNames::Employee_Home );
    // });

    Route::controller(EmployeeLeaveController::class)->group(function()
    {
        Route::get('/leave',                'index')->name( PortalRouteNames::Employee_Leave );
        Route::post('/xhr/leaves/getall',   'getLeaves')->name( PortalRouteNames::Employee_Leaves_Xhr_Get );
        Route::post('/xhr/leaves/request',  'requestLeave')->name( PortalRouteNames::Employee_Leaves_Xhr_Request );
        Route::post('/xhr/leaves/cancel',   'cancelLeave')->name( PortalRouteNames::Employee_Leaves_Xhr_Cancel );
    });
});

Route::middleware('guest:employee')
->prefix( PortalRouteNames::Employee_Route_Prefix )
->controller(EmployeeLoginController::class)
->group(function() 
{
    Route::get('/', 'index');

    Route::get('/login', 'index')->name( PortalRouteNames::Employee_Login );

    Route::post('/auth',  'authenticate')->name( PortalRouteNames::Employee_Auth );
});