<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestForm_Request;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;

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
            'editRoute'         => route(RouteNames::Leave['edit']),
            'approveRoute'      => route(RouteNames::Leave['approve']),
            'rejectRoute'       => route(RouteNames::Leave['reject']),
            'insertPostRoute'   => route(RouteNames::Leave['create']),
        ];

        // Dataset filters will be used for <select> dropdowns
        $defaultFilterValueAll = Constants::FILTER_VALUE_ALL;
        $defaultFilterTextAll  = Constants::RECORD_FILTER_ALL;
        $filterOptionAll       = [ $defaultFilterTextAll => $defaultFilterValueAll ];

        $datasetFilters = [
            'defaultText'  => $defaultFilterTextAll,
            'defaultValue' => $defaultFilterValueAll,
            'role'         => $filterOptionAll + array_flip(Employee::RoleToString),
            'leaveType'    => $filterOptionAll + LeaveRequest::getLeaveTypes(),
            'leaveStatus'  => $filterOptionAll + LeaveRequest::getLeaveStatuses()
        ];

        // These values are for the default modal form when creating leave request
        $defaultLeaveStatus = [
            'value' => LeaveRequest::LEAVE_STATUS_PENDING,
            'label' => LeaveRequest::LEAVE_PENDING
        ];

        return view('backoffice.leave.index')
            ->with('routes'             , $routes)
            ->with('datasetFilters'     , $datasetFilters)
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

    public function store(LeaveRequestForm_Request $request)
    {
        try 
        {
            $inputs = $request->validated();

            // Find the employee ID by the employee number
            $empId = Employee::where(Employee::f_EmpNo, '=', $inputs['idNo'])->value('id');

            if (!$empId)
                return Extensions::encodeFailMessage(Messages::EMPLOYEE_INEXISTENT);

            $updateId = $request->has('updateKey') && $request->filled('updateKey')
                      ? $this->hashids->decode($request->input('updateKey'))[0]
                      : null;

            $result = ($updateId) 
                    ? $this->update($inputs, $updateId)
                    : $this->insert($inputs, $empId);

            // Build row data that will be shown into the datatable
            $dataset = LeaveRequest::findLeaveRequest($result->id);

            if (empty($dataset))
                throw new ModelNotFoundException;

            $dataset['id'] = $this->hashids->encode($result['id']);

            return Extensions::encodeSuccessMessage('Success', ['rowData' => $dataset]);
        } 
        catch (ModelNotFoundException $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        }
        catch (Exception $ex) 
        {
            error_log($ex->getMessage() . ' at ' . $ex->getLine());
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);
        }
    }

    // edit() loads the record details that will be shown into the editor
    public function edit(Request $request)
    {
        $key = $request->input('rowKey');
        
        try 
        {
            $id = $this->hashids->decode($key);

            $leaveRequestFields = Extensions::prefixArray('l.', [
                LeaveRequest::f_StartDate   . ' as start',
                LeaveRequest::f_EndDate     . ' as end',
                LeaveRequest::f_LeaveStatus . ' as status',
                LeaveRequest::f_LeaveType   . ' as type'
            ]);

            $employeeFields = [
                'e.' . Employee::f_EmpNo . ' as idNo',
                Employee::getConcatNameDbRaw('e', 'empname', Constants::NAME_STYLE_EASTERN)
            ];

            $selectFields = array_merge($leaveRequestFields, $employeeFields);

            $leaveRequest = DB::table(LeaveRequest::getTableName() . ' as l')
                            ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'l.'.LeaveRequest::f_Emp_FK_ID)
                            ->select($selectFields)
                            ->where('l.id', '=', $id[0])
                            ->get()
                            ->firstOrFail();

            return Extensions::encodeSuccessMessage('Record information loaded for edit', [
                'data' => $leaveRequest
            ]);
        } 
        catch (ItemNotFoundException $ex) 
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        }
        catch (Exception $ex) 
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED); // . '\n\n' . $ex->getMessage());
        }
    }

    private function calculateLeaveDuration($leaveStart, $leaveEnd)
    {
        return $leaveStart == $leaveEnd 
            ? 1 
            : Carbon::parse($leaveStart)->diffInDays( Carbon::parse($leaveEnd) );
    }
    
    public function update($inputs, $updateId)
    {
        $leaveDuration = $this->calculateLeaveDuration($inputs['startDate'], $inputs['endDate']);
    
        $model = LeaveRequest::findOrFail($updateId);
        $model->update([
            LeaveRequest::f_StartDate   => $inputs['startDate'],
            LeaveRequest::f_EndDate     => $inputs['endDate'],
            LeaveRequest::f_LeaveType   => $inputs['leaveType'],
            LeaveRequest::f_Duration    => $leaveDuration == 1 ? "$leaveDuration day" : "$leaveDuration days",
            LeaveRequest::f_LeaveStatus => $inputs['leaveStatus']
        ]);
    
        return $model;
    }
    
    private function insert($inputs, $empId)
    {
        $leaveDuration = $this->calculateLeaveDuration($inputs['startDate'], $inputs['endDate']);
    
        return LeaveRequest::create([
            LeaveRequest::f_Emp_FK_ID   => $empId,
            LeaveRequest::f_StartDate   => $inputs['startDate'],
            LeaveRequest::f_EndDate     => $inputs['endDate'],
            LeaveRequest::f_LeaveType   => $inputs['leaveType'],
            LeaveRequest::f_Duration    => $leaveDuration == 1 ? "$leaveDuration day" : "$leaveDuration days",
            LeaveRequest::f_LeaveStatus => $inputs['leaveStatus']
        ]);
    }

    public function getRecords(Request $request)
    {
        $model = new LeaveRequest();

        return $model->getLeaveRequests($request);
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
