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

            $leaveId = $request->has('updateKey') && $request->filled('updateKey')
                     ? $this->hashids->decode($request->input('updateKey'))[0]
                     : null;

            $dataset = DB::transaction(function() use($leaveId, $inputs, $empId)
            {
                $result = [];

                // 0 -> create; 1 - update
                // This will be used in the frontend to determine which operation was done
                // such as Create or Update, which is necessary for displaying table rows
                $transaction = null;

                if ($leaveId)
                {
                    $result = $this->update($inputs, $leaveId);
                    $result = $result->toArray();

                    $startDate = Carbon::parse($result[LeaveRequest::f_StartDate]);
                    $endDate   = Carbon::parse($result[LeaveRequest::f_EndDate]);
                    $newStatus = Employee::ON_STATUS_DUTY;

                    $currentEmpStatus = Employee::where('id', $empId)->value(Employee::f_Status);
                    
                    // Update employee status to on leave upon updating the record.
                    // Only if the current date is within the range of the leave
                    // and if its current status is not the same as the new status.
                    if ( $result[LeaveRequest::f_LeaveStatus] == LeaveRequest::LEAVE_STATUS_APPROVED &&
                         now()->startOfDay()->between($startDate, $endDate)) 
                    {
                        $newStatus = Employee::ON_STATUS_LEAVE;
                    }

                    if ($currentEmpStatus != $newStatus)
                    {
                        $empStatus = DB::table(Employee::getTableName())
                            ->where('id', $empId)
                            ->update([Employee::f_Status => $newStatus]);
                     
                        // If the $updateStatus failed, we revert the changes
                        if ($empStatus < 1)
                            throw new ModelNotFoundException;
                    }

                    $transaction = 1; 
                }
                else 
                {
                    $result = $this->insert($inputs, $empId);
                    $result = $result->toArray();

                    $startDate = Carbon::parse($result[LeaveRequest::f_StartDate]);
                    $endDate   = Carbon::parse($result[LeaveRequest::f_EndDate]);

                    // Update employee status to on leave upon creating the record.
                    // Only if the current date is within the range of the leave.
                    // And only if the leave request was approved.
                    if ( $result[LeaveRequest::f_LeaveStatus] == LeaveRequest::LEAVE_STATUS_APPROVED && 
                         now()->startOfDay()->between($startDate, $endDate)) 
                    {
                        $empStatus = DB::table(Employee::getTableName())
                            ->where('id', $empId)
                            ->update([Employee::f_Status => Employee::ON_STATUS_LEAVE]);

                        // If the $updateStatus failed, we revert the changes
                        if ($empStatus < 1)
                            throw new ModelNotFoundException;
                    }

                    $transaction = 0;
                }

                // Build row data that will be shown into the datatable
                $rowData = LeaveRequest::findLeaveRequest($result['id']);

                if (empty($rowData))
                    throw new ModelNotFoundException;

                $rowData['id'] = $this->hashids->encode($result['id']);
                $rowData['transaction'] = $transaction;

                return $rowData;
            });

            return Extensions::encodeSuccessMessage('Success', ['rowData' => $dataset]);
        } 
        catch (ModelNotFoundException $ex){
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        }
        catch (Exception $ex) {
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
        catch (ItemNotFoundException $ex) {
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        }
        catch (Exception $ex) {
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);
        }
    }
    
    public function update($inputs, $updateId)
    {
        $leaveDuration = $this->calculateLeaveDuration($inputs['startDate'], $inputs['endDate']);
    
        $model = LeaveRequest::findOrFail($updateId);
        $model->update([
            LeaveRequest::f_StartDate   => $inputs['startDate'],
            LeaveRequest::f_EndDate     => $inputs['endDate'],
            LeaveRequest::f_LeaveType   => $inputs['leaveType'],
            LeaveRequest::f_Duration    => $leaveDuration, // == 1 ? "$leaveDuration day" : "$leaveDuration days",
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
            LeaveRequest::f_Duration    => $leaveDuration, // == 1 ? "$leaveDuration day" : "$leaveDuration days",
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
        return LeaveRequest::completeLeaveRequest('approve', $request);
    }
    
    public function rejectLeave(Request $request)
    {
        return LeaveRequest::completeLeaveRequest('reject', $request);
    }

    /**
    * When you use with('relation') without has('relation'), it behaves like a SQL LEFT JOIN. 
    * It retrieves all records from the first (or “left”) table, and the matched records from 
    * the second (or “right”) table. If there is no match, the result is NULL on the right side.
    * 
    * When you chain "::has('relation')" into "::with('relation')", it behaves more like a 
    * SQL INNER JOIN. It only retrieves records where there is a match in both tables.
    * 
    * The foreign key must also be included for this to work. In this case, we limit only the
    * selection of necessary fields to reduce server stress.
    */
    public function autoUpdateEmployeeLeaveStatus()
    {
        $today = now()->startOfDay();

        $employees = Employee::where(Employee::f_Status, '=', Employee::ON_STATUS_LEAVE)
        ->whereHas('leave_requests', function ($query) use ($today) {
            $query->where(LeaveRequest::f_LeaveStatus, '=', LeaveRequest::LEAVE_STATUS_APPROVED)
            ->where(LeaveRequest::f_StartDate, '<=', $today)
            ->where(LeaveRequest::f_EndDate, '>=', $today);
        })
        ->with(['leave_requests' => function ($query) use ($today) {
            $query->select([
                'id',
                LeaveRequest::f_Emp_FK_ID,
                LeaveRequest::f_StartDate,
                LeaveRequest::f_EndDate,
                LeaveRequest::f_LeaveStatus
            ])
            ->where(LeaveRequest::f_LeaveStatus, '=', LeaveRequest::LEAVE_STATUS_APPROVED);
        }])
        ->get();

        $employeesToUpdateOnDuty = [];
        $employeesToUpdateOnLeave = [];

        foreach ($employees as $employee) 
        {
            $onLeave = false; // Assume the employee is not on leave

            foreach ($employee->leave_requests as $leave_request) 
            {
                $start = Carbon::parse($leave_request[LeaveRequest::f_StartDate]);
                $end   = Carbon::parse($leave_request[LeaveRequest::f_EndDate]);

                // Check if today's date is within the leave request's date range
                if ($today->between($start, $end)) {
                    $onLeave = true; // The employee is on leave
                    break;
                }
            }

            // If the employee is on leave, add them to the on duty array
            // Otherwise, add them to the on leave array
            if ($onLeave) 
                $employeesToUpdateOnDuty[] = $employee->id;
            else
                $employeesToUpdateOnLeave[] = $employee->id; 
        }

        // Now, perform the update operations
        Employee::whereIn('id', $employeesToUpdateOnDuty)->update([Employee::f_Status => Employee::ON_STATUS_DUTY]);
        Employee::whereIn('id', $employeesToUpdateOnLeave)->update([Employee::f_Status => Employee::ON_STATUS_LEAVE]);

        // error_log('flag as ON DUTY');
        // error_log(print_r($employeesToUpdateOnDuty, true));
        
        // error_log('flag as ON LEAVE');
        // error_log(print_r($employeesToUpdateOnLeave, true));
        
        // Full LEFT JOIN:
        // $employees = Employee::with('leave_requests')->get();
    }

    private function calculateLeaveDuration($leaveStart, $leaveEnd) : string
    {
        $duration = $leaveStart == $leaveEnd 
                  ? 1 
                  : Carbon::parse($leaveStart)->diffInDays( Carbon::parse($leaveEnd) );

        $duration = ($duration == 1) ? "$duration day" : "$duration days";

        return $duration;
    }
}
