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
        'destroy'   => 'backoffice.teachers.destroy',
    ];

    public const Trails = [
        'index'     => 'backoffice.attendance.index',
        'all'       => 'backoffice.attendance.trails.all',
    ];

    public const Staff = [
        'index'     => 'backoffice.staff',
        'all'       => 'backoffice.staff.all',
        'create'    => 'backoffice.staff.create',
        'update'    => 'backoffice.staff.update',
        'details'   => 'backoffice.staff.details',
        'destroy'   => 'backoffice.staff.destroy',
    ];
}
