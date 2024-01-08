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
        'index'      => 'backoffice.attendance',
        'daily'      => 'backoffice.attendance.daily',
        'weekly'     => 'backoffice.attendance.weekly',
        'delete'     => 'backoffice.attendance.delete',
        'autoAbsent' => 'backoffice.attendance.auto-absent'
    ];

    public const Teachers = [
        'index'     => 'backoffice.teachers',
        'all'       => 'backoffice.teachers.all',
        'create'    => 'backoffice.teachers.create',
        'update'    => 'backoffice.teachers.update',
        'details'   => 'backoffice.teachers.details',
        'destroy'    => 'backoffice.teachers.destroy',
    ];

    public const Staff = [
        'index'     => 'backoffice.staff',
        'all'       => 'backoffice.staff.all',
        'create'    => 'backoffice.staff.create',
        'destroy'   => 'backoffice.staff.destroy',
    ];
}
