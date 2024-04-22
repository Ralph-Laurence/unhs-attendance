<?php

use App\Http\Controllers\portal\EmployeeAttendanceController;
use App\Http\Controllers\portal\EmployeeHomeController;
use App\Http\Controllers\portal\EmployeeLoginController;
use App\Http\Utils\PortalRouteNames;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:employee')
->prefix('/portal/employee')
->group(function()
{
    Route::controller(EmployeeLoginController::class)->group(function() 
    {
        Route::get("/logout", 'logout')->name( PortalRouteNames::Employee_Logout );
    });

    Route::controller(EmployeeAttendanceController::class)->group(function()
    {
        Route::get("/attendance", 'index')->name(PortalRouteNames::Employee_Attendance);
    });

    Route::controller(EmployeeHomeController::class)->group(function()
    {
        Route::get("/home",       'index')->name( PortalRouteNames::Employee_Home );
    });
});

Route::middleware('guest:employee')
->prefix('/portal/employee')
->controller(EmployeeLoginController::class)
->group(function() 
{
    Route::get('/', 'index');

    Route::get('/login', 'index')->name( PortalRouteNames::Employee_Login );

    Route::post('/auth',  'authenticate')->name( PortalRouteNames::Employee_Auth );
});