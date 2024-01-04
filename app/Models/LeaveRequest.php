<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const f_Emp_FK_ID    = 'emp_fk_id';
    public const f_StartDate    = 'start_date';
    public const f_EndDate      = 'end_date';
    public const f_LeaveType    = 'leave_type';
    public const f_Reason       = 'leave_reason';

    public static function getTableName() : string {
        return (new self)->getTable();
    }
    
    protected $guarded = [
        'id'
    ];
}
