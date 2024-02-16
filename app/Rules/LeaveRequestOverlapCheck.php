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
    protected $leaveRequestId ;    // Will be used only during update to ignore the checking on the same record (i.e itself)

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($employeeFK, $startDate, $endDate, $leaveRequestId = null)
    {
        $this->employeeFK = $employeeFK;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;

        $this->leaveRequestId = $leaveRequestId; // this will be null during creation
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

        $overlappingLeave = LeaveRequest::where(LeaveRequest::f_Emp_FK_ID, $this->employeeFK)

            // only check for overlapping leave requests that are not in a Rejected status
            ->where(LeaveRequest::f_LeaveStatus, '!=', LeaveRequest::LEAVE_STATUS_REJECTED)

            // Check for overlapping dates. A startDate or endDate that is within the existing
            // date range will be considered overlapping.
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
            });
        
        // Exclude the current leave request from the query only if leaveRequestId is not null
        if (!is_null($this->leaveRequestId))
            $overlappingLeave->where('id', '!=', $this->leaveRequestId);
    
        return !$overlappingLeave->exists();
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