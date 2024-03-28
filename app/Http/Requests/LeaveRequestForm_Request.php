<?php

namespace App\Http\Requests;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Rules\DateRangeCompare;
use App\Rules\LeaveRequestOverlapCheck;
use Hashids\Hashids;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveRequestForm_Request extends FormRequest
{
    protected $updateId = null;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $startDate = $this->input('startDate');
        $endDate   = $this->input('endDate');
        $dateRule  = [ 'date', 'required', 'date_format:' . Constants::DateFormat];

        if ($this->has('updateKey') && $this->filled('updateKey'))
        {
            $hashids  = new Hashids(LeaveRequest::HASH_SALT, LeaveRequest::MIN_HASH_LENGTH);
            $this->updateId = $hashids->decode($this->input('updateKey'))[0];
        }

        $rules = [
            'idNo' => [
                'required',
                Rule::exists(Employee::getTableName(), Employee::f_EmpNo),
            ],
            'startDate'     => $dateRule,
            'leaveType'     => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveTypes(true)),
            'leaveStatus'   => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveStatuses(true)),
            'endDate' => array_merge($dateRule, [
                new DateRangeCompare($startDate, $endDate),
                //new LeaveRequestOverlapCheck($empId, $startDate, $endDate, $updateId)
            ])
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator) 
        {
            $empId     = $this->input('idNo');
            $startDate = $this->input('startDate');
            $endDate   = $this->input('endDate');

            $rule = $this->checkOverlappingLeave($empId, $startDate, $endDate, $this->updateId);

            if ($rule)
            {
                $validator->errors()->add('startDate', Messages::LEAVE_REQUEST_OVERLAP);
                $validator->errors()->add('endDate',   Messages::LEAVE_REQUEST_OVERLAP);
            }
        });
    }

    private function checkOverlappingLeave($empNo, $startDate, $endDate, $updateId)
    {
        $employeeId = Employee::where(Employee::f_EmpNo, $empNo)->value('id');

        $overlappingLeave = LeaveRequest::where(LeaveRequest::f_Emp_FK_ID, $employeeId)

            // only check for overlapping leave requests that are not in a Rejected status
            ->where(LeaveRequest::f_LeaveStatus, '!=', LeaveRequest::LEAVE_STATUS_REJECTED)

            // Check for overlapping dates. A startDate or endDate that is within the existing
            // date range will be considered overlapping.
            ->where(function ($query) use ($startDate, $endDate) 
            {
                $f_start_date   = LeaveRequest::f_StartDate;
                $f_end_date     = LeaveRequest::f_EndDate;
                
                $query->whereBetween($f_start_date, [$startDate, $endDate])
                ->orWhereBetween($f_end_date,       [$startDate, $endDate])
                ->orWhere(function ($query) use ($startDate, $endDate, $f_start_date, $f_end_date) 
                {
                    $query->where($f_start_date, '<=', $startDate)
                          ->where($f_end_date,   '>=', $endDate);
                });
            });
        
        // Exclude the current leave request from the query only if leaveRequestId is not null
        if ($updateId)
            $overlappingLeave->where('id', '!=', $updateId);
    
        return $overlappingLeave->exists();
    }

    public function messages()
    {
        $errEndDate     = ValidationMessages::invalid('End Date');
        $errStartDate   = ValidationMessages::invalid('Start Date');
        
        return [
            'idNo.required'         => ValidationMessages::required('ID Number'),
            'idNo.exists'           => 'Unrecognized employee Id number.',

            'leaveType.required'    => ValidationMessages::required('Leave Type'),
            'leaveType.integer'     => ValidationMessages::invalid('Leave Type'),

            'leaveStatus.required'  => ValidationMessages::required('Leave Status'),
            'leaveStatus.integer'   => ValidationMessages::invalid('Leave Status'),

            'startDate.required'    => ValidationMessages::required('Start Date'),
            'endDate.required'      => ValidationMessages::required('End Date'),

            'startDate.date'        => $errStartDate,
            'startDate.date_format' => $errStartDate,
            
            'endDate.date'          => $errEndDate,
            'endDate.date_format'   => $errEndDate,
        ];
    }
}
