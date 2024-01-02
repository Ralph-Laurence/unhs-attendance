<?php

namespace App\Http\Utils;

class RouteNames
{
    public const DailyTimeRecord = [
        'index' => 'backoffice.daily-time-record.index'
    ];

    public const Scanner = [
        'index'     => 'dtr-scanner.index',
        'decode'    => 'dtr-scanner.decode',
        'history'   => 'dtr-scanner.history'
    ];

    public const Attendance = [
        'index'     => 'backoffice.attendance',
        'daily'     => 'backoffice.attendance.daily',
        'weekly'    => 'backoffice.attendance.weekly',
        'delete'    => 'backoffice.attendance.delete'
    ];
}
