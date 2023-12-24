<?php

use App\Http\Controllers\backoffice\DailyTimeRecordController;
use App\Http\Controllers\scanner\ScannerController;
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
    Route::post('/dtr-scanner/decode/', 'decode')->name(RouteNames::Scanner['decode']);
});

Route::controller(TestController::class)->group(function(){
    Route::get('/test', 'index');
});