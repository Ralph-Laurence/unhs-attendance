<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\portal\wrappers\EmployeeAttendance;
use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\PortalRouteNames;
use App\Models\Attendance;
use App\Models\Shared\Filters;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    public function index()
    {
        $routes = [
            'getAttendances'  => route(PortalRouteNames::Employee_Attendance_Xhr_Get),
        ];

        return view('portal.attendances.index')
            ->with('routes', $routes)
            ->with('monthFilters', Extensions::getMonthsAssoc());
    }

    /**
     * Select Attendance Records with the appropriate filters
     */
    public function getAttendances(Request $request)
    {     
        $model = new EmployeeAttendance;

        return $model->getMonthlyAttendances($request);
    }
}
