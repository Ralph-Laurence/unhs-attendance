<?php

namespace App\Http\Utils;

use App\Models\Employee;

class RouteNames
{
    public const Dashboard = [
        'root'      => 'backoffice.dashboard.root',
        'home'      => 'backoffice.dashboard.home',
        'index'     => 'backoffice.dashboard.index',
        'countEmp'  => 'backoffice.dashboard.count.emp',
        'countAttendance' => 'backoffice.dashboard.count.attendance',
        'attendanceStats' => 'backoffice.dashboard.stats.attendance',
        'leavereqtsStats' => 'backoffice.dashboard.stats.leavereqts',
    ];

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

    public const Absence = [
        'get'        => 'backoffice.absence.get',
        'index'      => 'backoffice.absence',
        'delete'     => 'backoffice.absence.delete'
    ];

    public const Late = [
        'get'        => 'backoffice.late.get',
        'index'      => 'backoffice.late',
        'delete'     => 'backoffice.late.delete'
    ];

    public const Leave = [
        'get'        => 'backoffice.leave.get',
        'index'      => 'backoffice.leave',
        'create'     => 'backoffice.leave.create',
        'delete'     => 'backoffice.leave.delete',
        'edit'       => 'backoffice.leave.edit',
        'approve'    => 'backoffice.leave.approve',
        'reject'     => 'backoffice.leave.reject'
    ];

    public const Employee = [
        'resendqr'   => 'backoffice.employee.resendqr',
        'list-empno' => 'backoffice.employee.list.empno',
        'get-empnos' => 'backoffice.employee.list.empnos',
    ];

    public const Faculty = [
        'index'     => 'backoffice.faculty',
        'all'       => 'backoffice.faculty.all',
        'edit'      => 'backoffice.faculty.edit',
        'create'    => 'backoffice.faculty.create',
        'update'    => 'backoffice.faculty.update',
        'show'      => 'backoffice.faculty.show',
        'destroy'   => 'backoffice.faculty.destroy',
    ];

    public const Staff = [
        'index'     => 'backoffice.staff',
        'all'       => 'backoffice.staff.all',
        'edit'      => 'backoffice.staff.edit',
        'create'    => 'backoffice.staff.create',
        'update'    => 'backoffice.staff.update',
        'show'      => 'backoffice.staff.show',
        'destroy'   => 'backoffice.staff.destroy',
    ];

    public const Guards = [
        'index'     => 'backoffice.guard',
        'all'       => 'backoffice.guard.all',
        'edit'      => 'backoffice.guard.edit',
        'create'    => 'backoffice.guard.create',
        'update'    => 'backoffice.guard.update',
        'show'      => 'backoffice.guard.show',
        'destroy'   => 'backoffice.guard.destroy',
    ];

    public const Trails = [
        'index'     => 'backoffice.attendance.index',
        'all'       => 'backoffice.attendance.trails.all',
        'exportPdf' => 'backoffice.attendance.trails.export.pdf',
    ];

    public const AuditTrails = [
        'index'     => 'backoffice.audits',
        'all'       => 'backoffice.audits.all',
        'show'      => 'backoffice.audits.show',
    ];

    public const AJAX = [

    ];
}
