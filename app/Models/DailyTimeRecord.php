<?php

namespace App\Models;

use App\Http\Utils\Extensions;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DailyTimeRecord extends Model
{
    use HasFactory;

    public const PERIOD_15TH_MONTH   = 'f';
    public const PERIOD_END_MONTH    = 'e';
    public const PERIOD_CURRENT      = 'c';
    public const PERIOD_OTHER_MONTH  = 'o';

    private const STR_PERIOD_15TH_MONTH  = 'First Half';
    private const STR_PERIOD_END_MONTH   = 'End Of Month';
    public  const STR_PERIOD_CURRENT     = 'This Month';
    private const STR_PERIOD_OTHER_MONTH = 'Other Months';

    public const MONTH_PERIODS   = [
        self::PERIOD_15TH_MONTH  => self::STR_PERIOD_15TH_MONTH,
        self::PERIOD_END_MONTH   => self::STR_PERIOD_END_MONTH,
        self::PERIOD_CURRENT     => self::STR_PERIOD_CURRENT,
        self::PERIOD_OTHER_MONTH => self::STR_PERIOD_OTHER_MONTH,
    ];

    public function fromFirstHalfMonth($empId)
    {
        $from = Carbon::now()->startOfMonth();
        //$to   = Carbon::now()->startOfMonth()->addDays(15);
        $to   = Carbon::now()->startOfMonth()->addDays(15)->subSecond();
        // subSecond includes the last timestamp before the next day. 
        // This ensures that the 15th day is always selected (i.e. 23:59:59)

        $dataset   = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();
        $daysRange = Extensions::getPeriods($from, $to);

        return [
            'dtr_range'  => self::STR_PERIOD_15TH_MONTH." ($daysRange)",
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->getStatistics($dataset)
        ];
    }

    public function fromCurrentMonth($empId)
    {
        $from = Carbon::now()->startOfMonth();
        $to   = Carbon::now()->endOfMonth();

        $dataset = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();

        $date = Carbon::now();
        $monthRange = Extensions::getMonthDateRange($date->month, $date->year);

        return [
            'dtr_range'  => self::STR_PERIOD_CURRENT.' ('. $monthRange['start'] ." - ". $monthRange['end'] .')',
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->getStatistics($dataset)
        ];
    }

    public function fromEndOfMonth($empId)
    {
        $from = Carbon::now()->startOfMonth()->addDays(15);
        $to   = Carbon::now()->endOfMonth();

        $dataset = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();

        $daysRange = Extensions::getPeriods($from, $to);

        return [
            'dtr_range'  => self::STR_PERIOD_END_MONTH." ($daysRange)",
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->getStatistics($dataset)
        ]; 
    }

    public function fromOtherMonth($empId, $monthNumber)
    {
        $year = Carbon::now()->year; // Get the current year
        $from = Carbon::create($year, $monthNumber, 1)->startOfMonth(); // Start of the specific month
        $to   = Carbon::create($year, $monthNumber, 1)->endOfMonth();   // End of the specific month

        $dataset = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();

        return [
            'dtr_range'  => "Month of " . $from->format('F Y'),
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->getStatistics($dataset)
        ];
    }

    private function buildSelectQuery(int $employeeId, Carbon $from, Carbon $to) : Builder
    {
        $seriesAlias    = 'dateseries';
        $col_status     = Attendance::f_Status;
        $statusPresent  = Attendance::STATUS_PRESENT;
        $statusBreak    = Attendance::STATUS_BREAK;
        $statusAbsent   = Attendance::STATUS_ABSENT;

        $tbl_attendance = Attendance::getTableName() . ' as a';
        $tbl_leave_reqs = LeaveRequest::getTableName().' as l';

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
            // Set the default status as Absent ('x') when null
            ->select([
                DB::raw("EXTRACT(DAY FROM $seriesAlias.date)      AS day_number"),
                DB::raw("DATE_FORMAT($seriesAlias.date, '%a')     AS day_name"),
                DB::raw("CASE
                    WHEN DATE_FORMAT($seriesAlias.date, '%w') IN (0, 6) THEN 'r'
                    WHEN $seriesAlias.date > CURDATE() THEN NULL
                    WHEN $col_status = '$statusPresent' THEN 'p'
                    WHEN $col_status = '$statusBreak'   THEN 'b'
                    WHEN $col_status = '$statusAbsent'  THEN 'x'
                    WHEN l.id IS NOT NULL THEN 'l'
                    ELSE COALESCE($col_status, 'x')
                END AS status"),

                $this->toShortTimeRaw(Attendance::f_TimeIn,     'am_in'),
                $this->toShortTimeRaw(Attendance::f_LunchStart, 'am_out' ),
                $this->toShortTimeRaw(Attendance::f_LunchEnd,   'pm_in'),
                $this->toShortTimeRaw(Attendance::f_TimeOut,    'pm_out'),

                // Unformatted duration strings
                'a.' . Attendance::f_Duration  . ' as duration_raw',
                'a.' . Attendance::f_OverTime  . ' as overtime_raw',
                'a.' . Attendance::f_UnderTime . ' as undertime_raw',
                'a.' . Attendance::f_Late      . ' as late_raw',

                // Formatted duration strings
                Attendance::timeStringToDurationRaw(Attendance::f_Duration , 'a'),
                Attendance::timeStringToDurationRaw(Attendance::f_Late     , 'a', 'late'),
                Attendance::timeStringToDurationRaw(Attendance::f_UnderTime, 'a', 'undertime'),
                Attendance::timeStringToDurationRaw(Attendance::f_OverTime , 'a', 'overtime'),
                'a.created_at',
            ])
            // Order the final result by date series in ascending
            ->orderBy(DB::raw("DATE($seriesAlias.date)"), 'asc');
        
        return $query;
    }

    private function toShortTimeRaw($time, $as = 'time', $includeMeridiem = true)
    {
        $format = "%l:%i %p";

        if ($includeMeridiem === false)
            $format = "%l:%i";

        $sql = "DATE_FORMAT($time, '$format') as $as";

        return DB::raw($sql);
    }

    //private function includeLeaveRequests($empid)

    private function getStatistics(array $dataset) : array
    {
        $totalWorkHrs   = Carbon::createFromTime(0, 0, 0);
        $totalOvertime  = Carbon::createFromTime(0, 0, 0);
        $totalUndertime = Carbon::createFromTime(0, 0, 0);
        $totalLateHrs   = Carbon::createFromTime(0, 0, 0);

        $totalPresent   = 0;
        $totalAbsent    = 0;
        $leaveCount     = 0;
    
        foreach($dataset as $data) 
        {
            if (!empty($data->duration_raw)) 
            {
                $workHrs = Carbon::createFromFormat('H:i:s', $data->duration_raw);

                $totalWorkHrs->addHours($workHrs->hour)
                    ->addMinutes($workHrs->minute)
                    ->addSeconds($workHrs->second);
            }

            if (!empty($data->overtime_raw))
            {
                $overtime = Carbon::createFromFormat('H:i:s', $data->overtime_raw);

                $totalOvertime->addHours($overtime->hour)
                        ->addMinutes($overtime->minute)
                        ->addSeconds($overtime->second);
            }

            if (!empty($data->undertime_raw))
            {
                $undertime = Carbon::createFromFormat('H:i:s', $data->undertime_raw);

                $totalUndertime->addHours($undertime->hour)
                        ->addMinutes($undertime->minute)
                        ->addSeconds($undertime->second);
            }

            if (!empty($data->late_raw))
            {
                $late = Carbon::createFromFormat('H:i:s', $data->late_raw);

                $totalLateHrs->addHours($late->hour)
                    ->addMinutes($late->minute)
                    ->addSeconds($late->second);
            }

            if (!empty($data->status))
            {
                switch ($data->status)
                {
                    case 'p': // Attendance::STATUS_PRESENT:
                        $totalPresent++;
                        break;

                    case 'x': // Attendance::STATUS_ABSENT:
                        $totalAbsent++;
                        break;

                    case 'l': //Employee::ON_STATUS_LEAVE:
                        $leaveCount++;
                        break;
                }
            }
        }

        return [
            'totalWorkHrs'      => $this->formatTimeDuration($totalWorkHrs),
            'totalLateHrs'      => $this->formatTimeDuration($totalLateHrs),
            'totalOvertime'     => $this->formatTimeDuration($totalOvertime),
            'totalUndertime'    => $this->formatTimeDuration($totalUndertime),
            'totalPresent'      => $totalPresent,
            'totalAbsent'       => $totalAbsent,
            'leaveCount'        => $leaveCount,

            // Will be used in frontend for additional styling 
            // of the 'status' field
            'statusMap' => [
                'x' => ['style' => 'status-absent'  , 'label' => Attendance::STATUS_ABSENT  ],
                'r' => ['style' => 'status-rest'    , 'label' => Attendance::STATUS_REST    ],
                'p' => ['style' => 'status-present' , 'label' => Attendance::STATUS_PRESENT ],
                'b' => ['style' => 'status-break'   , 'label' => Attendance::STATUS_BREAK   ],
                'l' => ['style' => 'status-leave'   , 'label' => Employee::ON_STATUS_LEAVE  ]
            ]
        ];
    }

    private function formatTimeDuration(Carbon $time) 
    {
        // Outputs like '4h 31m'
        if ($time->hour > 0)
            return $time->format('G\\h i\\m');

        // Outputs like '31m 24s'
        return $time->format('i\\m s\\s');
    }

    public function makePrintableData($employeeId, $from, $to) 
    {
        $adapter = $this->queryDtrForPrint($employeeId, $from, $to)->get();

        $totalUndertime = Carbon::createFromTime(0, 0, 0);

        foreach($adapter as $data) 
        {
            if (!empty($data->undertime_raw))
            {
                $undertime = Carbon::createFromFormat('H:i:s', $data->undertime_raw);

                $totalUndertime->addHours($undertime->hour)
                        ->addMinutes($undertime->minute)
                        ->addSeconds($undertime->second);
            }
        }

        $undertime = 0;

        if ($totalUndertime->hour > 0)
            $undertime = $totalUndertime->format('G\\h i\\m');
        else
            $undertime = $totalUndertime->format('i\\m');

        return [
            'dataset'   => $adapter,
            'undertime' => $undertime
        ];
    }

    private function queryDtrForPrint(int $employeeId, Carbon $from, Carbon $to) : Builder
    {
        $seriesAlias    = 'dateseries';
        $tbl_attendance = Attendance::getTableName() . ' as a';
        $tbl_leave_reqs = LeaveRequest::getTableName().' as l';

        $col_undertime  = Attendance::f_UnderTime;
        $statusLeave    = Employee::ON_STATUS_LEAVE;

        $query = DB::table(Extensions::getDateSeriesRaw($from, $to, $seriesAlias))
            // The attendances
            ->leftJoin($tbl_attendance, function($join) use($seriesAlias, $employeeId)
            {
                $join->on(DB::raw("DATE($seriesAlias.date)"), '=', DB::raw('DATE(created_at)'));
                $join->on('a.'.Attendance::f_Emp_FK_ID,       '=', DB::raw("$employeeId"));
            })
            // Include employee leave requests
            ->leftJoin($tbl_leave_reqs, function($join) use($seriesAlias, $employeeId)
            {
                $join->on(DB::raw("DATE($seriesAlias.date)"),  '>=', LeaveRequest::f_StartDate);
                $join->on(DB::raw("DATE($seriesAlias.date)"),  '<=', LeaveRequest::f_EndDate);
                $join->on('l.'.LeaveRequest::f_Emp_FK_ID,      '=',  DB::raw("$employeeId"));
                $join->where('l.'.LeaveRequest::f_LeaveStatus, '=',  LeaveRequest::LEAVE_STATUS_APPROVED);
            })
            ->select([
                DB::raw("EXTRACT(DAY FROM $seriesAlias.date) AS day_number"),

                DB::raw("CASE
                    WHEN DAYOFWEEK($seriesAlias.date) IN (1, 7) THEN DATE_FORMAT($seriesAlias.date, '%a')
                    WHEN l.id IS NOT NULL THEN '$statusLeave'
                    ELSE DATE_FORMAT(" . Attendance::f_TimeIn . ", '%l:%i')
                END as am_in"),

                'a.' . Attendance::f_UnderTime . ' as undertime_raw',

                $this->toShortTimeRaw(Attendance::f_LunchStart  , 'am_out'  ,  false),
                $this->toShortTimeRaw(Attendance::f_LunchEnd    , 'pm_in'   ,  false),
                $this->toShortTimeRaw(Attendance::f_TimeOut     , 'pm_out'  ,  false),

                DB::raw("CONCAT(HOUR(a.$col_undertime), 'h')   AS undertime_hours"),
                DB::raw("CONCAT(MINUTE(a.$col_undertime), 'm') AS undertime_minutes")
            ])
            // Order the final result by date series in ascending
            ->orderBy(DB::raw("DATE($seriesAlias.date)"), 'asc');

        return $query;
    }
}
