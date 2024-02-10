<?php

namespace App\Rules;

use App\Http\Text\Messages;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Validation\Rule;

class LeaveRequestOverlapCheck implements Rule
{
    protected $employeeFK;
    protected $startDate;
    protected $endDate;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($employeeFK, $startDate, $endDate)
    {
        $this->employeeFK = $employeeFK;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $start = $this->startDate;
        $end   = $this->endDate;

        // Check for overlapping dates
        $overlappingLeave = LeaveRequest::where(LeaveRequest::f_Emp_FK_ID, $this->employeeFK)
            ->where(function ($query) use ($start, $end) 
            {
                $f_start_date   = LeaveRequest::f_StartDate;
                $f_end_date     = LeaveRequest::f_EndDate;
    
                $query->whereBetween($f_start_date, [$start, $end])
                ->orWhereBetween($f_end_date,       [$start, $end])
                ->orWhere(function ($query) use ($start, $end, $f_start_date, $f_end_date) 
                {
                    $query->where($f_start_date, '<=', $start)
                          ->where($f_end_date,   '>=', $end);
                });
            })
        ->exists();
    
        return !$overlappingLeave;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Messages::LEAVE_REQUEST_OVERLAP;
    }
}