<?php
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

require __DIR__ . '/backoffice_admin.php';
require __DIR__ . '/portal_employees.php';
require __DIR__ . '/cron_jobs.php';

Route::get('/attention', function() 
{
    return response()->json([
        'status' => 1
    ]);
});