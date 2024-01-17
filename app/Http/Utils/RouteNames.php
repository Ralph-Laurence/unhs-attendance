<?php

namespace App\Http\Utils;

class RouteNames
{
    public const DailyTimeRecord = [
        'index'     => 'backoffice.attendance.dtr.index',
        'get'       => 'backoffice.attendance.dtr.get',
        'exportPdf' => 'backoffice.attendance.dtr.export-pdf',
    ];

    public const Scanner = [
        'index'     => 'dtr-scanner.index',
        'decode'    => 'dtr-scanner.decode',
        'authpin'   => 'dtr-scanner.auth.pin',
        'history'   => 'dtr-scanner.history'
    ];

    public const Attendance = [
        'get'        => 'backoffice.attendance.get',
        'index'      => 'backoffice.attendance',
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
        'exportPdf' => 'backoffice.attendance.trails.export.pdf',
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
