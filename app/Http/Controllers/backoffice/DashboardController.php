<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\AuditTrails;
use App\Models\Constants\FacultyConstants;
use App\Models\Constants\StaffConstants;
use App\Models\Employee;
use App\Models\Faculty;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const AttendanceStatSegmentFilters = [
        'Early Entry' => 'e',
        'On Time'     => 't',
        'Late'        => 'l',
        'Overtime'    => 'o',
        'Undertime'   => 'u',
    ];

    private const EmpStatusFilters = [
        'active' => 1,
        'leave'  => 2,
        'onduty' => 3,
        'out'    => 4
    ];

    public function index()
    {
        $routes = [
            'employeeCompare' => route(RouteNames::Dashboard['countEmp']),
            'attendanceStats' => route(RouteNames::Dashboard['countAttendance']),
            'leaveReqStats'   => route(RouteNames::Dashboard['leavereqtsStats']),
            'leaveRequests'   => route(RouteNames::Leave['index']),
            'empStats'        => route(RouteNames::Dashboard['empStats']),
            'allAudits'       => route(RouteNames::AuditTrails['index']),
        ];

        $allMonths = array_keys(Extensions::getMonthsAssoc());
        $allMonths = $allMonths[0].' to '.$allMonths[ count($allMonths) - 1 ].', '.date('Y');

        return view('backoffice.dashboard.index')
            ->with('routes', $routes)
            ->with('leaveReqFilters', [
                'a' => LeaveRequest::LEAVE_STATUS_APPROVED,
                'p' => LeaveRequest::LEAVE_STATUS_PENDING,
                'r' => LeaveRequest::LEAVE_STATUS_REJECTED,
                'u' => LeaveRequest::LEAVE_STATUS_UNNOTICED
            ])
            ->with('allMonths'          , $allMonths)
            ->with('recentActivity'     , $this->getRecentActivities())
            ->with('empStatusFilters'   , self::EmpStatusFilters);
    }

    private function getRecentActivities()
    {
        $select = [
            AuditTrails::f_Event        . ' as action',
            AuditTrails::f_Model_Type   . ' as affected',
        ];

        $actionIcons = AuditTrails::ActionIcons;

        $dataset = DB::table(AuditTrails::getTableName(), 'a')
                    ->leftJoin('users as e', 'e.id', '=', 'a.' . AuditTrails::f_User_FK)
                    ->select($select)
                    ->selectRaw("CONCAT(e.firstname,' ',e.lastname) AS user")
                    ->orderBy('a.created_at', 'desc')
                    ->limit(10)
                    ->get();

        foreach($dataset as $row)
        {
            if (method_exists($row->affected, 'getFriendlyName'))
                $row->affected = $row->affected::getFriendlyName();
            else
                $row->affected = 'Unknown';
            
            $row->actionIcon = $actionIcons[$row->action];
        }

        return $dataset;
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

        $pieSegmentRoutes = [
            Employee::STR_ROLE_TEACHER => route(RouteNames::Faculty['index']),
            Employee::STR_ROLE_STAFF   => route(RouteNames::Staff['index']),
            Employee::STR_ROLE_GUARD   => route(RouteNames::Guards['index']),
        ];

        $counts = collect($roles)->mapWithKeys(function ($role, $roleId) 
        {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Role, $roleId)
                ->count();
        
            return [$role => $count];
        })->toArray();
        
        return [
            'counts'    => $counts,
            'segments'  => $pieSegmentRoutes
        ];
    }

    private function countEmpStatusDifference()
    {
        // 1. Count Status Differences
        $statuses = [Employee::ON_STATUS_ACTIVE, Employee::ON_STATUS_LEAVE];

        $counts = collect($statuses)->mapWithKeys(function ($status) use(&$total) {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Status, $status)
                ->count();

            return [$status => $count];
        });
        
        $counts['ClockedIn']  = 0;
        $counts['ClockedOut'] = 0;

        
        // 2. Count On Duty vs Out
        $dataset_total = DB::table(Attendance::getTableName(), 'a')
            ->leftJoin(Employee::getTableName().' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
            ->select([
                'a.'.Attendance::f_TimeIn .' as ClockedIn',
                'a.'.Attendance::f_TimeOut.' as ClockedOut'
            ])
            ->whereDate('a.created_at', '=', date('Y-m-d'))
            ->get();

        foreach ($dataset_total as $totals)
        {
            // Those who have a clockout time, are identified
            // as out of office
            if (!empty($totals->ClockedOut))
            {
                $counts['ClockedOut'] += 1;
                continue;
            }

            // Those clocked in and have not clockedout yet,
            // are identified as On-Duty (In-Office)
            if (!empty($totals->ClockedIn) && empty($totals->ClockedOut))
                $counts['ClockedIn'] += 1;
        }

        // error_log(print_r($counts, true));

        return $counts->toArray();
    }

    private function countLeaveStatusDifference()
    {
        $statuses = LeaveRequest::getStatuses();
        $total    = 0;

        $counts = collect($statuses)->mapWithKeys(function ($status, $statusId) use(&$total) 
        {
            $count = DB::table(LeaveRequest::getTableName())
                ->where(LeaveRequest::f_LeaveStatus, $statusId)
                ->count();
        
            $total += $count;

            return [$status => $count];
        });
        
        $counts['Total'] = $total;

        return $counts->toArray();
    }

    public function getAttendanceGraphings(Request $request)
    {
        return response()->json([
            'attendanceStats'       => $this->getAttendanceStatistics(),
            'monthlyComparison'     => $this->getMonthlyAttendances(),
            'segmentAction'         => route(RouteNames::Dashboard['attendanceStats']),
            'segmentFilters'        => self::AttendanceStatSegmentFilters
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
        $f_timein   = Attendance::f_TimeIn;
        $f_overtime = Attendance::f_OverTime;
        $f_undertime = Attendance::f_UnderTime;

        $zeroTime   = Constants::ZERO_DURATION;

        $counts = DB::table(Attendance::getTableName(), 'a')
        ->select(
            //DB::raw('COUNT(*) as total_records'),
            DB::raw("SUM(CASE WHEN TIME(a.$f_timein) < '$beforeWork' THEN 1 ELSE 0 END) as 'Early Entry'"),
            DB::raw("SUM(CASE WHEN TIME(a.$f_timein) BETWEEN '$beforeWork' AND '$workStart' THEN 1 ELSE 0 END) as 'On Time'"),
            DB::raw("SUM(CASE WHEN TIME(a.$f_timein) > '$workStart' THEN 1 ELSE 0 END) as 'Late'"),
            //DB::raw("SUM(CASE WHEN TIME(a.$timeOut)   > '$curfew' THEN 1 ELSE 0 END) as 'Overtime'"),
            DB::raw("SUM(CASE WHEN 
                a.$f_overtime IS NOT NULL AND
                a.$f_overtime != '$zeroTime'
                THEN 1 ELSE 0 END) as 'Overtime'
            "),
            //DB::raw("SUM(CASE WHEN TIME(a.$timeOut)   < '$earlyExit' THEN 1 ELSE 0 END) as 'Undertime'")
            DB::raw("SUM(CASE WHEN 
                a.$f_undertime IS NOT NULL AND
                a.$f_undertime != '$zeroTime'
                THEN 1 ELSE 0 END) as 'Undertime'
            "),
        )
        ->leftJoin(Employee::getTableName().' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->whereDate('a.created_at', $today)
        ->where('e.'.Employee::f_Role, '!=', Employee::RoleGuard)
        ->first();

        return $counts;
    }

    private function getMonthlyAttendances()
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $records = DB::table(Attendance::getTableName())
        ->select(DB::raw('count(*) as total_records, DATE_FORMAT(created_at, "%b") as month'))
        ->groupBy('month')
        ->orderBy('created_at', 'ASC')
        ->where(Attendance::f_Status, '=', Attendance::STATUS_PRESENT)
        ->get()
        ->keyBy('month');

        $result = [];

        foreach ($months as $month) {
            $result[] = [
                'month' => $month,
                'total' => $records->has($month) ? $records[$month]->total_records : 0
            ];
        }

        return [
            'chartDatasource' => $result,
            'segmentRoute' => route(RouteNames::Dashboard['findMonthly'])
        ];
    }

    public function findMonthlyAttendance(Request $request)
    {
        $attendance = new Attendance();
        $records = $attendance->getMonthlyAttendances($request);

        return $records;
    }

    public function findAttxStatistics(Request $request)
    {
        $hasFilter = ($request->has('filter') || $request->filled('filter'));
        $filters   = array_values(self::AttendanceStatSegmentFilters);

        if ( !$hasFilter || ($hasFilter && !in_array($request->input('filter'), $filters)) )
            return response()->json(['code' => Constants::XHR_STAT_EMPTY]);

        $f_timein = 'a.'. Attendance::f_TimeIn;
        $f_undtim = 'a.'. Attendance::f_UnderTime;
        $f_ovrtim = 'a.'. Attendance::f_OverTime;

        $select = [ 
            'e.'.Employee::f_EmpNo .' as empno',
            'e.'.Employee::f_Role  .' as role',
            'e.'.Employee::f_Rank  .' as rank',
            Employee::getConcatNameDbRaw('e'),
        ];

        $query = DB::table(Attendance::getTableName(), 'a')
            ->leftJoin(Employee::getTableName() .' as e', 'e.id', '=', 'a.'. Attendance::f_Emp_FK_ID)
            ->whereDate('a.created_at', date('Y-m-d'))
            ->where('e.'.Employee::f_Role, '!=', Employee::RoleGuard)
            ->orderBy('a.created_at', 'desc');

        $filters = self::AttendanceStatSegmentFilters;
        $filter  = $request->input('filter');

        $dynamicCol = 'timein';

        $cond = [
            $filters['Early Entry' ] => function() use($query, $f_timein, &$select) {

                $select[] = Extensions::time_format_hip($f_timein, 'timein');

                return $query->whereTime( $f_timein, '<', Attendance::BEFORE_WORK_TIME )
                      ->select($select)
                      ->get();
            },
            $filters['On Time'] => function() use($query, $f_timein, &$select) {

                $select[] = Extensions::time_format_hip($f_timein, 'timein');

                return $query->where(function($q) use($f_timein) {
                        $q->whereRaw("TIME($f_timein) >= ?", [Attendance::BEFORE_WORK_TIME])
                          ->whereRaw("TIME($f_timein) <= ?", [Attendance::WORK_START_TIME]);
                      })
                     //->whereBetween( $f_timein, [Attendance::BEFORE_WORK_TIME, Attendance::WORK_START_TIME] )
                      ->select($select)
                      ->get();
            },
            $filters['Late'] => function() use($query, $f_timein, &$select) {

                $select[] = Extensions::time_format_hip($f_timein, 'timein');

                return $query->whereTime( $f_timein, '>', Attendance::WORK_START_TIME )
                      ->select($select)
                      ->get();
            },
            $filters['Overtime'] => function() use($query, $f_ovrtim, &$select, &$dynamicCol) {

                $select[] = Attendance::timeStringToDurationRaw($f_ovrtim, null, 'duration');
                $dynamicCol = 'duration';

                return $query->whereTime( $f_ovrtim, '>', '00:00:00')
                      ->select($select)
                      ->get();
            },
            $filters['Undertime'] => function() use($query, $f_undtim, &$select, &$dynamicCol) {

                $select[] = Attendance::timeStringToDurationRaw($f_undtim, null, 'duration');
                $dynamicCol = 'duration';

                return $query->whereTime($f_undtim , '>', '00:00:00')
                    ->select($select)
                    ->get();
            },
        ];

        $dataset      = $cond[ $filter ]();
        $facultyRanks = FacultyConstants::getRanks();
        $staffRanks   = StaffConstants::getRanks();

        // foreach ($dataset as $row)
        // {
        //     switch ($row->role)
        //     {
        //         case Employee::RoleTeacher:
        //             $row->rank = $facultyRanks[$row->rank];
        //             $row->role = Employee::STR_ROLE_TEACHER;
        //             break;

        //         case Employee::RoleStaff:
        //             $row->rank = $staffRanks[$row->rank];
        //             $row->role = Employee::STR_ROLE_STAFF;
        //             break;

        //         case Employee::RoleGuard:
        //             $row->role = Employee::STR_ROLE_GUARD;
        //             break;
        //     }

        //     unset($row->role);
        // }

        foreach ($dataset as $row) 
        {
            switch ($row->role) 
            {
                case Employee::RoleTeacher:
                    $row->rank = $facultyRanks[$row->rank];
                    break;

                case Employee::RoleStaff:
                    $row->rank = $staffRanks[$row->rank];
                    break;

                case Employee::RoleGuard:
                    $row->rank = Employee::STR_ROLE_GUARD;
                    break;
            }

            unset($row->role);
        }

        return response()->json([
            'message'   => 'OK',
            'dataset'   => $dataset,
            'segment'   => array_flip($filters)[$filter],
            'dynamic'   => $dynamicCol
        ]);
    }

    public function findLeaveStatistics(Request $request)
    {
        $hasFilter = ($request->has('segment') || $request->filled('segment'));
        $filters   = array_keys(LeaveRequest::getStatuses());

        if ( !$hasFilter || ($hasFilter && !in_array($request->input('segment'), $filters)) )
            return response()->json(['code' => Constants::XHR_STAT_EMPTY]);

        $filters = LeaveRequest::getStatuses();
        $filter  = $request->input('segment');

        $f_leaveStatus = 'l.'.LeaveRequest::f_LeaveStatus;

        $select = [
            'e.' . Employee::f_EmpNo . ' as empno',
            Employee::getConcatNameDbRaw('e'),
            //DB::raw( Extensions::mapCaseWhen($filters, $f_leaveStatus, 'status') ),
            Extensions::date_format_bdY(LeaveRequest::f_StartDate, 'date_from'),
            Extensions::date_format_bdY(LeaveRequest::f_EndDate, 'date_to'),
            DB::raw( Extensions::mapCaseWhen(LeaveRequest::getTypes(), 'l.'.LeaveRequest::f_LeaveType, 'type') ),
            LeaveRequest::f_Duration .' as duration'
        ];

        $dataset = DB::table(LeaveRequest::getTableName(), 'l')
            ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'l.' . LeaveRequest::f_Emp_FK_ID)
            ->select($select)
            ->where($f_leaveStatus, '=', $filter)
            ->orderBy('l.created_at', 'desc')
            ->get();

        $segmentColors = [
            LeaveRequest::LEAVE_STATUS_APPROVED     => '#00D1A4',
            LeaveRequest::LEAVE_STATUS_PENDING      => '#FF840C',
            LeaveRequest::LEAVE_STATUS_REJECTED     => '#FF2641',
            LeaveRequest::LEAVE_STATUS_UNNOTICED    => '#FF8620' 
        ];

        return response()->json([
            'msg' => 'OK',
            'dataset' => $dataset,
            'segment' => $filters[$filter],
            'segmentColor' => $segmentColors[$filter]
        ]);
    }

    public function findEmpStatus(Request $request)
    {
        if ($request->has('status'))
        {
            $filter  = $request->input('status'); //self::EmpStatusFilters[];
            $dataset = $this->_getEmpStatDiff($filter);

            return response()->json([
                'msg' => 'OK',
                'dataset' => $dataset['data'],
                'segment' => $dataset['segment'],
                'dynamic' => $dataset['dynamic']
            ]);
        }
    }

    private function _getEmpStatDiff($status)
    {
        // Query for getting the difference between Employee active status
        $employeeSelect = [
            Employee::f_EmpNo .' as empno',
            Employee::f_Role  .' as role',
            Employee::f_Rank  .' as rank',
            DB::raw(Employee::getConcatNameDbRaw('', 'empname', Constants::NAME_STYLE_EASTERN))
        ];

        $queryEmpActiveStat = Employee::select($employeeSelect)->orderBy(Employee::f_LastName);
        
        // Query to retrieve the employees who are in-office vs out of office
        $queryEmpAttendanceStat = DB::table(Attendance::getTableName().' as a')
            ->leftJoin(Employee::getTableName().' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
            ->whereDate('a.created_at', '=', date('Y-m-d'));
        
        $segment = '';  // This will be a segment title that will be shown into the modal 'segment context'
        $dynamic = '';  // This tells jquery datatables that this column may be added or not.

        $dataset = [
            self::EmpStatusFilters['active'] => function() use($queryEmpActiveStat, &$segment) 
            {   
                $segment = 'Active Employees';

                return $queryEmpActiveStat
                        ->where(Employee::f_Status, '=', Employee::ON_STATUS_ACTIVE)
                        ->get();
            },
            self::EmpStatusFilters['leave' ] => function() use($queryEmpActiveStat, &$segment)
            {
                $segment = 'Employees On Leave';

                return $queryEmpActiveStat
                        ->where(Employee::f_Status, '=', Employee::ON_STATUS_LEAVE)
                        ->get();
            },
            self::EmpStatusFilters['onduty'] => function() use ($queryEmpAttendanceStat, $employeeSelect, &$segment, &$dynamic) 
            {
                $segment = 'Employees On Duty';
                $dynamic = 'timein';

                return $queryEmpAttendanceStat->select(array_merge($employeeSelect, [
                           DB::raw(Extensions::time_format_hip('a.'.Attendance::f_TimeIn, 'timein')),
                           'a.id',
                       ]))
                       ->where('a.'.Attendance::f_TimeIn, '!=', '')
                       ->where('a.'.Attendance::f_TimeOut, '=', NULL)
                       ->get();
            },
            self::EmpStatusFilters['out'   ] => function() use ($queryEmpAttendanceStat, $employeeSelect, &$segment, &$dynamic) 
            {
                $segment = 'Clocked Out Employees';
                $dynamic = 'timeout';

                return $queryEmpAttendanceStat->select(array_merge($employeeSelect, [
                           DB::raw(Extensions::time_format_hip('a.'.Attendance::f_TimeOut, 'timeout')),
                           'a.id',
                       ]))
                       ->where('a.'.Attendance::f_TimeOut, '!=', '')
                       ->get();
            },
        ];

        $dataset = $dataset[$status]();

        $facultyRanks = FacultyConstants::getRanks();
        $staffRanks   = StaffConstants::getRanks();

        foreach ($dataset as $row) 
        {
            switch ($row->role) 
            {
                case Employee::RoleTeacher:
                    $row->rank = $facultyRanks[$row->rank];
                    break;

                case Employee::RoleStaff:
                    $row->rank = $staffRanks[$row->rank];
                    break;

                case Employee::RoleGuard:
                    $row->rank = Employee::STR_ROLE_GUARD;
                    break;
            }

            unset($row->role);
        }

        return [
            'data'      => $dataset,
            'segment'   => $segment,
            'dynamic'   => $dynamic
        ];
    }
}
