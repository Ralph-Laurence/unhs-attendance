<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\RouteNames;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Shared\Filters;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveRequestsController extends Controller
{
    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids(LeaveRequest::HASH_SALT, LeaveRequest::MIN_HASH_LENGTH);
    }
    
    public function index()
    {
        $routes = [
            'ajax_get_all'      => route(RouteNames::Leave['get']),
            'ajax_load_empids'  => route(RouteNames::AJAX['list-empno']),
            'deleteRoute'       => route(RouteNames::Leave['delete']),
            'insertPostRoute'   => route(RouteNames::Leave['create']),
            'updatePostRoute'   => ''
        ];

        // Role filters will be used for <select> dropdowns
        $roleFilters = array_values(Employee::RoleToString);

        // Hash option values
        $leaveTypes = array_map(function($value) 
        {
            return $this->hashids->encode($value);
        }, 
        LeaveRequest::getLeaveTypes());

        return view('backoffice.leave.index')
            ->with('routes'             , $routes)
            ->with('roleFilters'        , $roleFilters)
            ->with('leaveTypes'         , $leaveTypes)
            ->with('monthOptions'       , Extensions::getMonthsAssoc());
    }

    public function destroy(Request $request)
    {

    }

    public function store(Request $request)
    {
        $inputs = $this->validateFields($request);

        if ($inputs['validation_stat'] == 400)
            return json_encode($inputs);

        // Find the employee ID by the employee number
        $empId = Employee::where(Employee::f_EmpNo, '=', $inputs['input-id-no'])->value('id');

        $startDate = $inputs['input-leave-start'];
        $endDate   = $inputs['input-leave-end'];

        if ($this->leaveOverlaps($empId, $startDate, $endDate))
            return Extensions::encodeFailMessage(Messages::LEAVE_REQUEST_OVERLAP);
            //response()->json(['error' => ], 400);

        try 
        {
            $insert = DB::transaction(function() use($inputs)
            {

                $leaveStart = $inputs['input-leave-start']; 
                $leaveEnd   = $inputs['input-leave-end'];

                $leaveDuration = $leaveStart == $leaveEnd ? 1 :
                                 Carbon::parse($leaveStart)->diffInDays( Carbon::parse($leaveEnd) );

                return LeaveRequest::create([
                    LeaveRequest::f_Emp_FK_ID   => $inputs['input-id-no'],
                    LeaveRequest::f_StartDate   => $leaveStart,
                    LeaveRequest::f_EndDate     => $leaveEnd,
                    LeaveRequest::f_LeaveType   => $inputs['input-leave-type'],
                    LeaveRequest::f_Duration    => $leaveDuration == 1 ? "$leaveDuration day" : "$leaveDuration days",
                    LeaveRequest::f_LeaveStatus => LeaveRequest::LEAVE_STATUS_APPROVED
                ]);
            });

            //if ($insert)

            return Extensions::encodeSuccessMessage('Success', $insert->toArray());
        } 
        catch (\Throwable $th) 
        {
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED . '\n\n' . $th->getMessage());
        }
    }

    public function getRecords(Request $request)
    {
        //$selectRange = $request->input('range');

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        // if (!in_array($selectRange, Filters::getDateRangeFilters(), true))
        //     return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $model = new LeaveRequest();

        $dataset = $model->getLeaveRequests($request); //$transactions[$selectRange];
        
        return $dataset;
    }

    private function validateFields(Request $request)
    {
        $errEndDate     = ValidationMessages::invalid('End Date');
        $errStartDate   = ValidationMessages::invalid('Start Date');

        $validationMessages = 
        [
            'input-id-no.required'          => ValidationMessages::required('ID Number'),
            'input-id-no.exists'            => 'Unrecognized employee Id number.',

            'input-leave-type.required'     => ValidationMessages::required('Leave Type'),

            'input-leave-start.required'    => ValidationMessages::required('Start Date'),
            'input-leave-end.required'      => ValidationMessages::required('End Date'),

            'input-leave-start.date'        => $errStartDate,
            'input-leave-start.date_format' => $errStartDate,
            
            'input-leave-end.date'          => $errEndDate,
            'input-leave-end.date_format'   => $errEndDate,
        ];
        
        $validationFields = array(
            'input-id-no' => [
                'required',
                Rule::exists(Employee::getTableName(), Employee::f_EmpNo),
            ],
            'input-leave-start' => 'required|date_format:Y-m-d|date',
            'input-leave-end'   => 'required|date_format:Y-m-d|date',
            'input-leave-type'  => 'required'
        );

        $validator = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator->fails())
            return $this->sendValidationFailed( ['errors' => $validator->errors()] );

        // Decode the leave type then make sure that it exists in the Leave Types array
        $leaveType = $this->hashids->decode( $request->input('input-leave-type') );

        $err_leaveType = $this->sendValidationFailed(['errors' => array
        (
            'input-leave-type' => ValidationMessages::invalid('Leave Type')
        )]);

        // Decode failed or does not exist in the arrays
        if (!$leaveType || (!in_array($leaveType[0], LeaveRequest::getLeaveTypes(true))))
            return $err_leaveType;

        // Assume successful validation
        $validated = $validator->validated();

        // Apply the decoded leave type
        $validated['input-leave-type'] = $leaveType[0];

        // Validation Passed
        return array_merge( $validated, ['validation_stat' => Constants::ValidationStat_Success] );
    }

    private function sendValidationFailed($extraData = []) 
    {
        return ['validation_stat' => Constants::ValidationStat_Failed] + $extraData;
    }

    private function leaveOverlaps($employeeFK, $startDate, $endDate) : bool 
    {
        // Check for overlapping dates
        $overlappingLeave = LeaveRequest::where(LeaveRequest::f_Emp_FK_ID, $employeeFK)
            ->where(function ($query) use ($startDate, $endDate) 
            {
                $f_start_date = LeaveRequest::f_StartDate;
                $f_end_date = LeaveRequest::f_EndDate;

                $query->whereBetween($f_start_date, [$startDate, $endDate])
                ->orWhereBetween($f_end_date, [$startDate, $endDate])
                ->orWhere(function ($query) use ($startDate, $endDate, $f_start_date, $f_end_date) 
                {
                    $query->where($f_start_date, '<', $startDate)
                        ->where($f_end_date, '>', $endDate);
                });
            })
        ->exists();

        return $overlappingLeave;
    }
}
