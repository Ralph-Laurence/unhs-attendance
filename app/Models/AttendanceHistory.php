<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistory extends Model
{
    use HasFactory;

    public const FLAG_REGULAR   = 'Regular';    // Regular Work Days
    public const FLAG_HOLIDAY   = 'Holiday';
    public const FLAG_REST_DAY  = 'Rest Day';   // Weekends
    public const FLAG_EVENT     = 'Event';      // Special Events

    public const STATUS_PRESENT = 'Present';
    public const STATUS_ABSENT  = 'Absent';
    public const STATUS_DAY_OFF = 'Day Off';    // Day off can be the rest days, holidays or not scheduled for work
    public const STATUS_LEAVE   = 'Leave';
    
    public const f_EmpNo    = 'emp_no';
    public const f_Status   = 'status';
    public const f_Flag     = 'flag';
}
