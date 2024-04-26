<?php

namespace App\Http\Controllers\portal\wrappers;

use App\Http\Utils\Extensions;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EmployeeAttendance 
{
    public function getMonthlyAttendances(Request $request)
    {
        $monthNumber = ($request->has('month') && $request->filled('month')) ? 
                      $request->input('month') : // Month from input
                      Carbon::now()->month;      // This month

        // $dataset = $this->buildAttendanceQuery()
        //     ->whereMonth('created_at', '=', $monthIndex)
        //     ->get();

        $dataset = $this->buildSelectQuery($monthNumber)->get();

        $monthName = Carbon::createFromFormat('!m', $monthNumber)->monthName;

        $styles = [
            'r' => [ 'label' => 'Rest', 'style' => 'rem-r' ],
            'p' => [ 'label' => Attendance::STATUS_PRESENT,  'style' => 'rem-p' ],
            'b' => [ 'label' => Attendance::STATUS_BREAK,    'style' => 'rem-b' ],
            'x' => [ 'label' => Attendance::STATUS_ABSENT,   'style' => 'rem-x' ],
            'l' => [ 'label' => Employee::ON_STATUS_LEAVE,   'style' => 'rem-l' ],
        ];

        return json_encode([
            'data'          => $dataset,
            'month'         => "Month of $monthName",
            'remarkStyles'  => $styles
        ]);
    }

    /**
    * Base query builder for retrieving attendances 
    */
    private function buildAttendanceQuery()
    {
        $fields = [
            Extensions::date_format_bdY('created_at', 'date'),

            Extensions::time_format_hip(Attendance::f_TimeIn    , 'am_in'),
            Extensions::time_format_hip(Attendance::f_LunchStart, 'am_out'),
            Extensions::time_format_hip(Attendance::f_LunchEnd  , 'pm_in'),
            Extensions::time_format_hip(Attendance::f_TimeOut   , 'pm_out'),

            Attendance::timeStringToDurationRaw(Attendance::f_Duration,  null, 'duration'),
            Attendance::timeStringToDurationRaw(Attendance::f_OverTime,  null, 'overtime'),
            Attendance::timeStringToDurationRaw(Attendance::f_UnderTime, null, 'undertime'),
            Attendance::timeStringToDurationRaw(Attendance::f_Late,      null, 'late'),
            Attendance::f_Status.' as remarks',
        ];

        $sql = DB::table(Attendance::getTableName())
               ->where(Attendance::f_Emp_FK_ID, '=',  Auth::id())
               ->orderBy('created_at', 'desc')
               ->select($fields);

        return $sql;
    }

    private function buildSelectQuery($monthNumber) : Builder
    {
        $seriesAlias    = 'dateseries';
        $col_status     = Attendance::f_Status;
        $statusPresent  = Attendance::STATUS_PRESENT;
        $statusBreak    = Attendance::STATUS_BREAK;
        $statusAbsent   = Attendance::STATUS_ABSENT;

        $tbl_attendance = Attendance::getTableName() . ' as a';
        $tbl_leave_reqs = LeaveRequest::getTableName().' as l';
        
        $employeeId     = Auth::id();

        $year = Carbon::now()->year; // Get the current year
        $from = Carbon::create($year, $monthNumber, 1)->startOfMonth(); // Start of the specific month
        $to   = Carbon::create($year, $monthNumber, 1)->endOfMonth();   // End of the specific month

        $query = DB::table(Extensions::getDateSeriesRaw($from, $to, $seriesAlias))
            // The attendances
            ->leftJoin($tbl_attendance, function($join) use($seriesAlias, $employeeId)
            {
                $join->on(DB::raw("DATE($seriesAlias.date)"), '=', DB::raw('DATE(created_at)'));
                $join->on('a.'.Attendance::f_Emp_FK_ID,       '=', DB::raw("?"))->addBinding($employeeId);
            })
            // Include his leave requests
            ->leftJoin($tbl_leave_reqs, function($join) use($seriesAlias, $employeeId)
            {
                $join->on(DB::raw("DATE($seriesAlias.date)"),  '>=', LeaveRequest::f_StartDate);
                $join->on(DB::raw("DATE($seriesAlias.date)"),  '<=', LeaveRequest::f_EndDate);
                $join->on('l.'.LeaveRequest::f_Emp_FK_ID,      '=',  DB::raw("?"))->addBinding($employeeId);
                $join->where('l.'.LeaveRequest::f_LeaveStatus, '=',  LeaveRequest::LEAVE_STATUS_APPROVED);
            })
            // Include the employee's created_at date
            ->leftJoin(Employee::getTableName().' as e', function ($join) use ($employeeId) {
                $join->on('e.id', '=', DB::raw("?"))->addBinding($employeeId);
            })

            // Set the default status as Absent ('x') when null
            ->select([
                // DB::raw("EXTRACT(DAY FROM $seriesAlias.date)      AS day_number"),
                // DB::raw("DATE_FORMAT($seriesAlias.date, '%a')     AS day_name"),
                Extensions::date_format_bdY("$seriesAlias.date", 'date'),
                
                DB::raw("CASE
                    WHEN DATE_FORMAT($seriesAlias.date, '%w') IN (0, 6) THEN 'r'
                    WHEN $seriesAlias.date > CURDATE() THEN NULL
                    WHEN DATE($seriesAlias.date) < DATE(e.created_at) THEN NULL
                    WHEN a.$col_status = '$statusPresent' THEN 'p'
                    WHEN a.$col_status = '$statusBreak'   THEN 'b'
                    WHEN a.$col_status = '$statusAbsent'  THEN 'x'
                    WHEN l.id IS NOT NULL THEN 'l'
                    ELSE COALESCE(a.$col_status, 'x')                    
                END AS remarks"), // status

                Extensions::time_format_hip_join(Attendance::f_TimeIn    , 'am_in',  'a'),
                Extensions::time_format_hip_join(Attendance::f_LunchStart, 'am_out', 'a'),
                Extensions::time_format_hip_join(Attendance::f_LunchEnd  , 'pm_in',  'a'),
                Extensions::time_format_hip_join(Attendance::f_TimeOut   , 'pm_out', 'a'),

                // Formatted duration strings
                Attendance::timeStringToDurationRaw(Attendance::f_Duration , 'a'),
                Attendance::timeStringToDurationRaw(Attendance::f_Late     , 'a', 'late'),
                Attendance::timeStringToDurationRaw(Attendance::f_UnderTime, 'a', 'undertime'),
                Attendance::timeStringToDurationRaw(Attendance::f_OverTime , 'a', 'overtime'),
                'a.created_at',
            ])
            // Order the final result by date series in ascending
            ->orderBy(DB::raw("DATE($seriesAlias.date)"), 'desc');
        
        return $query;
    }
}