<?php

namespace App\Http\Utils;

class PortalRouteNames
{
    public const Employee_Request       = 'employee';
    public const Employee_Route_Prefix  = '/portal/employee';
    public const Employee_Prefix        = 'portal.employee';
    public const Employee_Login         = 'portal.employee.login';
    public const Employee_Logout        = 'portal.employee.logout';
    public const Employee_Auth          = 'portal.employee.auth';
    public const Employee_Home          = 'portal.employee.home';
    public const Employee_Leave         = 'portal.employee.leave';
    public const Employee_Attendance    = 'portal.employee.attendance';

    public const Employee_Leaves_Xhr_Get        = 'xhr.portal.employee.leaves';
    public const Employee_Leaves_Xhr_Request    = 'xhr.portal.employee.leaves.request';
    public const Employee_Leaves_Xhr_Cancel     = 'xhr.portal.employee.leaves.cancel';

    public const Employee_Attendance_Xhr_Get    = 'xhr.portal.employee.attendance';
}