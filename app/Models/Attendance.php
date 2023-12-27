<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

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

    public const STATUS_PRESENT = 'Present';
    public const STATUS_BREAK   = 'Break';
    public const STATUS_ABSENT  = 'Absent';

    public static function createTimeIn(int $empId) : Attendance
    {
        $insert = Attendance::create([
            self::f_Emp_FK_ID   => $empId,
            self::f_TimeIn      => Carbon::now(),
            self::f_Status      => self::STATUS_PRESENT,
        ]);

        return $insert;
    }

    public static function getTableName() : string {
        return (new self)->getTable();
    }

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