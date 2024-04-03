<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Models\Shared\Filters;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Attendance extends Model
{
    use HasFactory;

    private $hashids = null;

    public const f_Emp_FK_ID    = 'emp_fk_id';      // Employee Foreign Key ID
    public const f_TimeIn       = 'time_in';
    public const f_LunchStart   = 'lunch_start';
    public const f_TimeOut      = 'time_out';
    public const f_LunchEnd     = 'lunch_end';
    public const f_Status       = 'status';         // Status -> Present, Break, Absent
    public const f_Duration     = 'duration';
    public const f_UnderTime    = 'undertime';
    public const f_OverTime     = 'overtime';
    public const f_Late         = 'late';
    public const f_WeekNo       = 'week_no';

    public const STATUS_PRESENT = 'Present';
    public const STATUS_BREAK   = 'Lunch Break';
    public const STATUS_ABSENT  = 'Absent';
    public const STATUS_LATE    = 'Late';
    public const STATUS_REST    = 'Rest';
    
    public const STATUS_UNDERTIME = 'Undertime';
    public const STATUS_OVERTIME  = 'Overtime';

    public const BEFORE_WORK_TIME = '07:15:00';     // 7:15 AM      -> All attendances within this time is "On Time"
    public const WORK_START_TIME  = '08:00:00';     // 8:00 AM      -> All attendances after this time is "Late"
    public static $lunchOverTime  = '12:10:00';     // 12:10 PM     -> Extra work after this time is added to overtime
    public const EARLY_DISMISSAL  = '16:30:00';     // 4:30 PM      -> Will not calculate undertime after this time
    public const CURFEW           = '17:30:00';     // 5:30 PM      -> All employees fully dismissed

    public const HASH_SALT = 'FA610E'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    
    protected $appends = ['hashid'];

    protected $guarded = [
        'id'
    ];
    
    public static function getTableName() : string {
        return Constants::TABLE_ATTENDANCES;
    }

     // This is the friendly name that will be used when 
    // presenting this model in the Audits table.
    public static function getFriendlyName() : string {
        return 'Attendances';
    }

    public static function createTimeIn(int $empId, bool $ignoreLate = false) : Attendance
    {
        $timeIn = Carbon::now();

        $data = [
            self::f_Emp_FK_ID   => $empId,
            self::f_TimeIn      => $timeIn,
            self::f_Status      => self::STATUS_PRESENT,
            self::f_WeekNo      => Carbon::now()->weekOfYear
        ];

        $workStart  = Carbon::parse(self::WORK_START_TIME);

        if ($timeIn->gt(self::WORK_START_TIME))
        {
            if ($ignoreLate === false)
            {
                $late = $timeIn->diffInSeconds($workStart) / 3600;
                $data[Attendance::f_Late] = Extensions::durationToTimeString($late);
            }
            else
                $data[Attendance::f_Late] = Constants::ZERO_DURATION;
            
        }

        $insert = Attendance::create($data);

        return $insert;
    }

    public static function getIconClasses() : array {
        return [
            Attendance::STATUS_PRESENT     => 'present',
            Attendance::STATUS_BREAK       => 'break',
            Attendance::STATUS_UNDERTIME   => 'undertime',
            Attendance::STATUS_ABSENT      => 'absent',
            Attendance::STATUS_LATE        => 'late'
        ];
    }

    protected $casts = [
        self::f_TimeIn      => 'datetime',
        self::f_LunchStart  => 'datetime',
        self::f_LunchEnd    => 'datetime',
        self::f_TimeOut     => 'datetime',
        // self::f_Duration    => 'datetime',
        // self::f_UnderTime   => 'datetime',
        // self::f_OverTime    => 'datetime',
        // self::f_Late        => 'datetime',
    ];

    public function getHashidAttribute() {

        if ( is_null($this->hashids) )
            $hashids = new \Hashids\Hashids(); // Create a new instance of Hashids

        return $hashids->encode($this->attributes['id']); // Use the instance to call the encode 
    }

    public static function timeStringToDurationRaw($field = 'duration', $joinAlias = null, $caseAlias = 'duration')
    {
        $field = $joinAlias ? "$joinAlias.$field" : $field;

        $sql = DB::raw("CASE
                   WHEN HOUR($field)   != 0 THEN CONCAT(HOUR($field),   'h ', MINUTE($field), 'm')
                   WHEN MINUTE($field) != 0 THEN CONCAT(MINUTE($field), 'm ', SECOND($field), 's')
                   ELSE CONCAT(SECOND($field), 'secs')
               END as $caseAlias");
        
        return $sql;
    }
    

    /**
     * Select all employees who do not have an attendance record for the current day, 
     * then insert an “Absent” record for each of them in the attendance table.
     * We will exclude those who are on leave.
     */
    public static function autoAbsentEmployees()
    {
        $tblAttendance = self::getTableName();

        $currentDate = Carbon::now();

        $employees = DB::table(Employee::getTableName() . ' as e')
            ->leftJoin("$tblAttendance as a", function ($join) use ($currentDate) 
            {
                $join->on('e.id', '=', 'a.' . Attendance::f_Emp_FK_ID)
                ->whereBetween('a.created_at', 
                [
                    $currentDate->startOfDay()->format(Constants::TimestampFormat), 
                    $currentDate->endOfDay()->format(Constants::TimestampFormat)
                ]);
            })
        ->whereNull('a.id')
        ->where('e.'.Employee::f_Status, '!=', Employee::ON_STATUS_LEAVE)
        ->where('e.'.Employee::f_Role, '!=', Employee::RoleGuard)
        ->select('e.id')
        ->get();

        // For each employee without an attendance record, insert an "Absent" record
        foreach ($employees as $employee) 
        {
            DB::table($tblAttendance)->insert([
                Attendance::f_Emp_FK_ID => $employee->id,
                Attendance::f_Status    => Attendance::STATUS_ABSENT,
                Attendance::f_WeekNo    => Carbon::now()->weekOfYear
            ]);
        }
    }

    /**
     * Retrieve all attendances that were made today
     */
    public function getDailyAttendances(Request $request)
    {
        // The current timestamp
        $currentDate = Carbon::now();

        // Instead of whereDate($today), we will use where between
        $dataset = $this->buildAttendanceQuery()
            ->whereBetween('a.created_at', 
            [
                $currentDate->startOfDay()->format(Constants::TimestampFormat), 
                $currentDate->endOfDay()->format(Constants::TimestampFormat)
            ]);

        $this->applyRoleFilter($request, $dataset);
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);
        
        return $this->encodeAttendanceData($request, $dataset, 'Today');
    }

    public function getWeeklyAttendances(Request $request)
    {
        $currentWeek = Carbon::now()->weekOfYear;

        $dataset = $this->buildAttendanceQuery()
                   ->where('a.' . Attendance::f_WeekNo, '=', $currentWeek);

        $this->applyRoleFilter($request, $dataset);
        
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);

        return $this->encodeAttendanceData($request, $dataset, "This Week (week #$currentWeek)");
    }

    public function getMonthlyAttendances(Request $request)
    {
        if (!$request->filled('monthIndex'))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $monthIndex = $request->input('monthIndex');

        $dataset = $this->buildAttendanceQuery()
            ->whereMonth('a.created_at', '=', $monthIndex);
        
        $this->applyRoleFilter($request, $dataset);
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);

        $monthName = Carbon::createFromFormat('!m', $monthIndex)->monthName;

        return $this->encodeAttendanceData($request, $dataset, "Month of $monthName");
    }

    private function applyRoleFilter(Request &$request, &$dataset)
    {
        if (
            ($request->has('role') && $request->filled('role')) && 
            ($request->input('role') != Constants::RECORD_FILTER_ALL)
        )
        {
            $role  = $request->input('role');
            $roles = Employee::RoleToString; 
            
            if (!in_array($role, $roles))
            {
                $request->replace(['role' => Constants::RECORD_FILTER_ALL]);
                return;
            }

            $dataset->where('e.'.Employee::f_Role, '=', array_flip($roles)[ $role ]);
        }
    }

    /**
    * Base query builder for retrieving attendances 
    */
    private function buildAttendanceQuery()
    {
        $fields = array_merge([
            Employee::getConcatNameDbRaw('e'), 
            $this->timeStringToDurationRaw(self::f_Duration, 'a')
        ],
        Extensions::prefixArray('a.', [
            'id', 
            'created_at',
            self::f_TimeIn  . ' as timein',
            self::f_TimeOut . ' as timeout',
            self::f_Status  . ' as status',
        ]));

        $sql = DB::table(self::getTableName(), 'a')
               ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.self::f_Emp_FK_ID)
               ->where('a.'.self::f_Status, '!=', self::STATUS_ABSENT)
               ->orderBy('a.created_at', 'desc')
               ->select($fields);

        return $sql;
    }

     /**
     * Encode the datasets into JSON that will be sent as AJAX response
     */
    private function encodeAttendanceData(Request &$request, $dataset, $descriptiveRange = null)
    {
        $filters = [
            'select_range' => $request->input('range')
        ];

        if ($request->has('monthIndex'))
            $filters['month_index'] = $request->input('monthIndex');

        if ($request->has('role'))
            $filters['select_role'] = $request->input('role');
        else
            $filters['select_role'] = Constants::RECORD_FILTER_ALL;

        return json_encode([
            'data'      => $dataset->toArray(),
            'range'     => $descriptiveRange,
            'filters'   => $filters,
            'icon'      => Attendance::getIconClasses()
        ]);
    }
}