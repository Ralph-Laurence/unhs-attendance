<?php

namespace App\Http\Requests;

use App\Http\Utils\Constants;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Rules\DateRangeCompare;
use App\Rules\LeaveRequestOverlapCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequestForm extends FormRequest
{
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
        $empId     = $this->input('idNo');
        $updateId  = $this->input('updateKey');
        $dateRule  = [ 'date', 'required', 'date_format:' . Constants::DateFormat];

        return [
            'idNo' => [
                'required',
                Rule::exists(Employee::getTableName(), Employee::f_EmpNo),
            ],
            'startDate'     => $dateRule,
            'leaveType'     => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveTypes(true)),
            'leaveStatus'   => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveStatuses(true)),
            'endDate' => array_merge($dateRule, [
                new DateRangeCompare($startDate, $endDate),
                new LeaveRequestOverlapCheck($empId, $startDate, $endDate, $updateId)
            ])
        ];
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
