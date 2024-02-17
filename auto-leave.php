<?php

// This script must be executed in a batch script
// where the windows task scheduler can later run
// the batch script

require __DIR__.'/vendor/autoload.php'; // Adjust this path based on your directory structure

$app = require_once __DIR__.'\\bootstrap\\app.php'; // Adjust this path based on your directory structure

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Call your controller method
app()->make(\App\Http\Controllers\backoffice\LeaveRequestsController::class)->autoUpdateEmployeeLeaveStatus();