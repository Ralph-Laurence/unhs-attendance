<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $routes = [
            'employeeCompare' => route(RouteNames::Dashboard['countEmp']),
            'attendanceStats' => route(RouteNames::Dashboard['countAttendance'])
        ];

        return view('backoffice.dashboard.index')
            ->with('routes', $routes);
    }

    public function getEmpGraphings(Request $request)
    {
        return response()->json([
            'employeeDifference'    => $this->countEmployeesDifference(),
            'empStatusDifference'   => $this->countEmpStatusDifference(),
            'leaveStatusDifference' => $this->countLeaveStatusDifference()
        ]);
    }

    private function countEmployeesDifference()
    {
        $roles = array_flip(Employee::getRoles());

        $counts = collect($roles)->mapWithKeys(function ($role, $roleId) 
        {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Role, $roleId)
                ->count();
        
            return [$role => $count];
        })->toArray();
        
        return $counts;
    }

    private function countEmpStatusDifference()
    {
        $statuses = [Employee::ON_STATUS_DUTY, Employee::ON_STATUS_LEAVE];

        $counts = collect($statuses)->mapWithKeys(function ($status) {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Status, $status)
                ->count();
        
            return [$status => $count];
        })->toArray();
        
        return $counts;
    }

    private function countLeaveStatusDifference()
    {
        $statuses = LeaveRequest::getStatuses();

        $counts = collect($statuses)->mapWithKeys(function ($status, $statusId) 
        {
            $count = DB::table(LeaveRequest::getTableName())
                ->where(LeaveRequest::f_LeaveStatus, $statusId)
                ->count();
        
            return [$status => $count];
        })->toArray();
        
        return $counts;
    }

    public function getAttendanceGraphings(Request $request)
    {
        return response()->json([
            'attendanceStats'       => $this->getAttendanceStatistics(),
            'monthlyComparison'     => $this->getMonthlyAttendances()
        ]);
    }

    private function getAttendanceStatistics()
    {
        $today      = date('Y-m-d');
        $timeOut    = Attendance::f_TimeOut;
        $beforeWork = Attendance::BEFORE_WORK_TIME;
        $workStart  = Attendance::WORK_START_TIME; 
        $curfew     = Attendance::CURFEW;
        $earlyExit  = Attendance::EARLY_DISMISSAL;

        $counts = DB::table(Attendance::getTableName())
        ->select(
            //DB::raw('COUNT(*) as total_records'),
            DB::raw("SUM(CASE WHEN TIME(created_at) < '$beforeWork' THEN 1 ELSE 0 END) as 'Early Entry'"),
            DB::raw("SUM(CASE WHEN TIME(created_at) BETWEEN '$beforeWork' AND '$workStart' THEN 1 ELSE 0 END) as 'On Time'"),
            DB::raw("SUM(CASE WHEN TIME(created_at) > '$workStart' THEN 1 ELSE 0 END) as 'Late'"),
            DB::raw("SUM(CASE WHEN TIME($timeOut)   > '$curfew' THEN 1 ELSE 0 END) as 'Overtime'"),
            DB::raw("SUM(CASE WHEN TIME($timeOut)   < '$earlyExit' THEN 1 ELSE 0 END) as 'Undertime'")
        )
        ->whereDate('created_at', $today)
        ->first();

        return $counts;
    }

    private function getMonthlyAttendances()
    {
        // $counts = DB::table(Attendance::getTableName())
        //     ->select(DB::raw('count(*) as total_records, DATE_FORMAT(created_at, "%b") as month'))
        //     ->groupBy('month')
        //     ->orderBy('created_at', 'ASC')
        //     ->get();

        // return $counts;

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $records = DB::table(Attendance::getTableName())
        ->select(DB::raw('count(*) as total_records, DATE_FORMAT(created_at, "%b") as month'))
        ->groupBy('month')
        ->orderBy('created_at', 'ASC')
        ->get()
        ->keyBy('month');

        $result = [];

        foreach ($months as $month) {
            $result[] = [
                'month' => $month,
                'total' => $records->has($month) ? $records[$month]->total_records : 0
            ];
        }

        return $result;
    }
}
