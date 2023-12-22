<?php

namespace App\Models;

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
}
