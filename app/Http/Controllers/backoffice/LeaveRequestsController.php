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
use App\Rules\DateRangeCompare;
use App\Rules\LeaveRequestOverlapCheck;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            'approveRoute'      => route(RouteNames::Leave['approve']),
            'rejectRoute'       => route(RouteNames::Leave['reject']),
            'insertPostRoute'   => route(RouteNames::Leave['create']),
            'updatePostRoute'   => ''
        ];

        // Role filters will be used for <select> dropdowns
        $defaultFilterValueAll = Constants::FILTER_VALUE_ALL;
        $defaultFilterTextAll  = Constants::RECORD_FILTER_ALL;
 
        $roleFilters = [
            'defaultText'  => $defaultFilterTextAll,
            'defaultValue' => $defaultFilterValueAll,
            'filterItems'  => [ $defaultFilterTextAll => $defaultFilterValueAll ] + array_flip(Employee::RoleToString)
        ];

        $leaveFilters = [
            'defaultText'  => $defaultFilterTextAll,
            'defaultValue' => $defaultFilterValueAll,
            'filterItems'  => [ $defaultFilterTextAll => $defaultFilterValueAll ] + LeaveRequest::getLeaveTypes()
        ];

        $statusFilters = [
            'defaultText'  => $defaultFilterTextAll,
            'defaultValue' => $defaultFilterValueAll,
            'filterItems'  => [ $defaultFilterTextAll => $defaultFilterValueAll ] + LeaveRequest::getLeaveStatuses()
        ];

        $defaultLeaveStatus = [
            'value' => LeaveRequest::LEAVE_STATUS_PENDING,
            'label' => LeaveRequest::LEAVE_PENDING
        ];

        return view('backoffice.leave.index')
            ->with('routes'             , $routes)
            ->with('roleFilters'        , $roleFilters)
            ->with('leaveFilters'       , $leaveFilters)
            ->with('statusFilters'      , $statusFilters)
            ->with('defaultLeaveStatus' , $defaultLeaveStatus)
            ->with('leaveTypes'         , LeaveRequest::getLeaveTypes())
            ->with('leaveStatuses'      , LeaveRequest::getLeaveStatuses())
            ->with('monthOptions'       , Extensions::getMonthsAssoc());
    }

    public function destroy(Request $request)
    {
        $key = $request->input('rowKey');
        $id  = $this->hashids->decode($key);
        
        return LeaveRequest::dissolve($id[0]);
    }

    public function store(Request $request)
    {
        $inputs = $this->validateFields($request);

        if ($inputs['code'] == Constants::ValidationStat_Failed)
            return json_encode($inputs);

        $inputs = $inputs['data'];
        
        // Find the employee ID by the employee number
        $empId = Employee::where(Employee::f_EmpNo, '=', $inputs['idNo'])->value('id');
       
        if (!$empId)
            return Extensions::encodeFailMessage(Messages::EMPLOYEE_INEXISTENT);

        $leaveStart = $inputs['startDate'];
        $leaveEnd   = $inputs['endDate'];

        try 
        {
            $insert = DB::transaction(function() use($inputs, $empId, $leaveStart, $leaveEnd)
            {
                $leaveDuration = $leaveStart == $leaveEnd ? 1 :
                                 Carbon::parse($leaveStart)->diffInDays( Carbon::parse($leaveEnd) );

                $create = LeaveRequest::create([
                    LeaveRequest::f_Emp_FK_ID   => $empId,
                    LeaveRequest::f_StartDate   => $leaveStart,
                    LeaveRequest::f_EndDate     => $leaveEnd,
                    LeaveRequest::f_LeaveType   => $inputs['leaveType'],
                    LeaveRequest::f_Duration    => $leaveDuration == 1 ? "$leaveDuration day" : "$leaveDuration days",
                    LeaveRequest::f_LeaveStatus => $inputs['leaveStatus']
                ]);

                // Build row data that will be shown into the datatable
                $dataset = LeaveRequest::getInsertedRow($create->id);

                if (empty($dataset))
                    throw new ModelNotFoundException;

                $dataset['id'] = $this->hashids->encode($create['id']);
                
                return $dataset;
            });

            return Extensions::encodeSuccessMessage('Success', ['rowData' => $insert]);
        } 
        catch (ModelNotFoundException $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL . '\n\n' . $ex->getMessage());
        }
        catch (Exception $ex) 
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED); // . '\n\n' . $ex->getMessage());
        }
    }

    public function getRecords(Request $request)
    {
        $model = new LeaveRequest();

        return $model->getLeaveRequests($request);
    }

    private function validateFields(Request $request)
    {
        $errEndDate     = ValidationMessages::invalid('End Date');
        $errStartDate   = ValidationMessages::invalid('Start Date');

        $startDate = $request->input('startDate');
        $endDate   = $request->input('endDate');
        $empId     = $request->input('idNo');
        $dateRule  = [ 'date', 'required', 'date_format:' . Constants::DateFormat];

        $validationMessages = 
        [
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

        // Validation Step 1 -> Basic Checking
        $validationFields = array(
            'idNo' => [
                'required',
                Rule::exists(Employee::getTableName(), Employee::f_EmpNo),
            ],
            'startDate'     => $dateRule,
            'leaveType'     => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveTypes(true)),
            'leaveStatus'   => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveStatuses(true))
        );

        $validator1 = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator1->fails())
            return Extensions::validationFailResponse($validator1);

        // Validation Step 2 -> Complex Date Checking
        $validator2 = Validator::make($request->all(), [
            'endDate' => array_merge($dateRule, [
                new DateRangeCompare($startDate, $endDate),
                new LeaveRequestOverlapCheck($empId, $startDate, $endDate)
            ])
        ],
        $validationMessages);

        if ($validator2->fails())
            return Extensions::validationFailResponse($validator2);

        // Assume successful validation
        return Extensions::validationSuccessResponse(array_merge(
            $validator1->validated(), $validator2->validated()
        ));
    }

    public function approveLeave(Request $request)
    {
        return LeaveRequest::completeLeaveRequest('0', $request);
    }
    
    public function rejectLeave(Request $request)
    {
        return LeaveRequest::completeLeaveRequest('-1', $request);
    }
}
