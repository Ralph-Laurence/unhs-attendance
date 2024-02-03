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
    public const STATUS_BREAK   = 'Lunch';
    public const STATUS_ABSENT  = 'Absent';
    public const STATUS_LATE    = 'Late';
    
    public const STATUS_UNDERTIME = 'Undertime';
    public const STATUS_OVERTIME  = 'Overtime';

    public static $workStartTime  = '07:30:00';     // 7:30 AM      -> All attendances must be made before this time
    public static $lunchOverTime  = '12:10:00';     // 12:10 PM     -> Extra work after this time is added to overtime
    public static $earlyDismissal = '16:50:00';     // 4:50 PM      -> Will not calculate undertime after this time
    public static $workExitTime   = '17:30:00';     // 5:30 PM      -> All employees fully dismissed

    public const HASH_SALT = 'FA610E'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    public static function createTimeIn(int $empId) : Attendance
    {
        $timeIn = Carbon::now();

        $data = [
            self::f_Emp_FK_ID   => $empId,
            self::f_TimeIn      => $timeIn,
            self::f_Status      => self::STATUS_PRESENT,
        ];

        $workStart  = Carbon::parse(Attendance::$workStartTime);

        if ($timeIn->gt(Attendance::$workStartTime))
        {
            $late = $timeIn->diffInSeconds($workStart) / 3600;
            $data[Attendance::f_Late] = self::formatTimeDuration($late);
        }

        $insert = Attendance::create($data);

        return $insert;
    }

    public static function getTableName() : string {
        return (new self)->getTable();
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

    protected $appends = ['hashid'];

    protected $guarded = [
        'id'
    ];

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
    /** 
     * Will output something like 1H 30mins 2secs
    */
    public static function formatTimeDuration($duration)
    {
        $hours   = floor($duration);
        $minutes = floor(($duration - $hours) * 60);
        $seconds = floor((($duration - $hours) * 60 - $minutes) * 60);
    
        $result = '';
        if ($hours > 0)
            $result .= $hours . 'Hr' . ($hours > 1 ? 's' : '') . ' ';
        
        if ($minutes > 0)
            $result .= $minutes . 'min' . ($minutes > 1 ? 's' : '') . ' ';
        
        if ($seconds > 0)
            $result .= $seconds . 'sec' . ($seconds > 1 ? 's' : '');
    
        return trim($result);
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
        ->select('e.id')
        ->get();

        // For each employee without an attendance record, insert an "Absent" record
        foreach ($employees as $employee) 
        {
            DB::table($tblAttendance)->insert([
                Attendance::f_Emp_FK_ID => $employee->id,
                Attendance::f_Status    => Attendance::STATUS_ABSENT
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
        $currentWeek = Extensions::getCurrentWeek();

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

            $dataset->where('e.'.Employee::f_Position, '=', array_flip($roles)[ $role ]);
        }
    }

    /**
    * Base query builder for retrieving attendances 
    */
    private function buildAttendanceQuery()
    {
        $fname = Employee::f_FirstName;
        $mname = Employee::f_MiddleName;
        $lname = Employee::f_LastName;

        $employeeFields = [
            DB::raw("CONCAT_WS(' ', e.$fname, NULLIF(e.$mname, ''), e.$lname) as empname")
        ];
        
        $attendanceFields = Extensions::prefixArray('a.', [
            Attendance::f_TimeIn   . ' as timein',
            Attendance::f_TimeOut  . ' as timeout',
            Attendance::f_Duration . ' as duration',
            Attendance::f_Status   . ' as status',
            'id',
            'created_at' ,
        ]);

        $fields = array_merge($attendanceFields, $employeeFields);
        $query = DB::table(self::getTableName() . ' as a')
                ->select($fields)
                ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
                ->where('a.' . Attendance::f_Status, '!=', Attendance::STATUS_ABSENT)
                ->orderBy('a.created_at', 'desc');

        return $query;
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

/**
    // If time_out is provided, calculate duration, undertime, overtime, and late
    // if ($attendance->time_out) 
    // {
    //     $workHours = Carbon::parse($attendance->time_in)->diffInHours(Carbon::parse($attendance->time_out));
    //     $lunchHours = Carbon::parse($attendance->lunch_start)->diffInHours(Carbon::parse($attendance->lunch_end));
    //     $duration = $workHours - $lunchHours;

    //     $undertime = $workHours < 8 ? 8 - $workHours : 0;
    //     $overtime = $workHours > 8 ? $workHours - 8 : 0;
    //     $late = Carbon::parse($attendance->time_in)->gt(Carbon::parse('08:00:00')) ? Carbon::parse($attendance->time_in)->diffInSeconds(Carbon::parse('08:00:00')) : 0;

    //     $attendance->update([
    //         'duration' => $duration,
    //         'undertime' => $undertime,
    //         'overtime' => $overtime,
    //         'late' => $late,
    //     ]);
    // }
 */