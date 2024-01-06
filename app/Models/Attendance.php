<?php

namespace App\Models;

use App\Http\Utils\Constants;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    public const STATUS_BREAK   = 'Break';
    public const STATUS_ABSENT  = 'Absent';
    
    public const STATUS_UNDERTIME = 'Undertime';
    public const STATUS_OVERTIME  = 'Overtime';

    public static $workStartTime  = '07:30:00';     // 7:30 AM      -> All attendances must be made before this time
    public static $lunchOverTime  = '12:10:00';     // 12:10 PM     -> Extra work after this time is added to overtime
    public static $earlyDismissal = '16:50:00';     // 4:50 PM      -> Will not calculate undertime after this time
    public static $workExitTime   = '17:30:00';     // 5:30 PM      -> All employees fully dismissed

    // public static function createTimeIn(int $empId) : Attendance
    // {
    //     $insert = Attendance::create([
    //         self::f_Emp_FK_ID   => $empId,
    //         self::f_TimeIn      => Carbon::now(),
    //         self::f_Status      => self::STATUS_PRESENT,
    //     ]);

    //     return $insert;
    // }
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
            Attendance::STATUS_ABSENT      => 'absent'
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
     */
    public static function autoAbsentEmployees()
    {
        $tblAttendance = Attendance::getTableName();

        // Get today's date
        $today = Carbon::today();
        $currentDate = Carbon::now();

        // Get all employees who do not have an attendance record 
        // for today and are not on leave
        $employees = DB::table(Employee::getTableName() . ' as e')
            ->leftJoin("$tblAttendance as a", function ($join) use ($today, $currentDate) 
            {
                $join->on('e.id', '=', 'a.' . Attendance::f_Emp_FK_ID)
                //->whereDate('a.created_at', '=', $today);
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