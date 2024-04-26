<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\backoffice\LeaveRequestsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\portal\wrappers\EmployeeLeave;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\PortalRouteNames;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Rules\DateRangeCompare;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeLeaveController extends Controller
{
    public function index()
    {
        $routes = [
            'getLeaves'     => route(PortalRouteNames::Employee_Leaves_Xhr_Get),
            'requestNew'    => route(PortalRouteNames::Employee_Leaves_Xhr_Request),
            'cancelLeave'   => route(PortalRouteNames::Employee_Leaves_Xhr_Cancel)
        ];

        $leaveFilters = [
            'All'        => Constants::FILTER_VALUE_ALL,
            'This Month' => 1
        ];

        return view('portal.leave.index')
            ->with('leaveTypes',   LeaveRequest::getLeaveTypes())
            ->with('routes',       $routes)
            ->with('leaveFilters', $leaveFilters);
    }

    public function getLeaves(Request $request)
    {
        $leave = new EmployeeLeave();
        return $leave->getLeaveRequests($request);
    }

    public function requestLeave(Request $request)
    {
        $startDate = $request->input('input-leave-start');
        $endDate   = $request->input('input-leave-end');
        $dateRule  = [ 'date', 'required', 'date_format:' . Constants::DateFormat];
        
        $validator = Validator::make($request->all(), [
            'input-leave-start' => $dateRule,
            'input-leave-end'   => array_merge($dateRule, [ new DateRangeCompare($startDate, $endDate) ]),
            'input-leave-type'  => 'required|integer|in:' . implode(',', LeaveRequest::getLeaveTypes(true)),
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], Constants::ValidationStat_Failed);

        $overlap = $this->checkOverlappingLeave($request->input('input-leave-start'), $request->input('input-leave-end'));

        if ($overlap) {
            $validator->errors()->add( 'input-leave-start', Messages::LEAVE_REQUEST_OVERLAP );
            $validator->errors()->add( 'input-leave-end'  , Messages::LEAVE_REQUEST_OVERLAP );

            return response()->json(['errors' => $validator->errors()], Constants::ValidationStat_Failed);
        }
        
        try 
        {
            $create = DB::transaction(function() use($validator) {

                $input      = $validator->validated();
                $leaveType  = $input['input-leave-type'];
                $leaveStat  = LeaveRequest::LEAVE_STATUS_PENDING;
                $startDate  = $input['input-leave-start'];
                $endDate    = $input['input-leave-end'];
                $isPending  = $leaveStat == LeaveRequest::LEAVE_STATUS_PENDING ? 1 : 0;
                $duration   = LeaveRequestsController::calculateLeaveDuration($startDate, $endDate);
                $insert     = LeaveRequest::create([
                    LeaveRequest::f_Emp_FK_ID   => Auth::id(),  
                    LeaveRequest::f_StartDate   => $startDate,
                    LeaveRequest::f_EndDate     => $endDate,
                    LeaveRequest::f_LeaveType   => $leaveType,
                    LeaveRequest::f_Duration    => $duration,
                    LeaveRequest::f_LeaveStatus => $leaveStat
                ]);

                $frontendData = [
                    'date_from'     => Extensions::Ymd_to_MdY($startDate),
                    'date_to'       => Extensions::Ymd_to_MdY($endDate),
                    'duration'      => ucwords($duration),   
                    'type'          => array_flip(LeaveRequest::getLeaveTypes())[$leaveType],
                    'status'        => array_flip(LeaveRequest::getLeaveStatuses())[$leaveStat],     
                    'request_date'  => date('M d, Y'),
                    'isPending'     => $isPending,
                    'id'            => EmployeeLeave::hashId($insert->id)
                ];

                return $frontendData;
            });

            return Extensions::encodeSuccessMessage('Leave request successfully added.', ['rowData' => $create]);
        } 
        catch (ModelNotFoundException $ex) {
            // Handle the error when no employee or leave request record is found
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        } 
        catch (Exception $ex) 
        {
            error_log($ex->getMessage() . ' at ' . $ex->getLine());
            return Extensions::encodeFailMessage(Messages::GENERIC_INSERT_FAIL);
        }
    }

    private function checkOverlappingLeave($startDate, $endDate)
    {
        $overlappingLeave = LeaveRequest::where(LeaveRequest::f_Emp_FK_ID, Auth::id())

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
        
        return $overlappingLeave->exists();
    }

    public function cancelLeave(Request $request)
    {
        $failMessage = Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);
        $validator   = Validator::make($request->all(), [ 'rowKey' => 'required|string' ]);

        if ($validator->fails())
            return Extensions::encodeFailMessage(Messages::UPDATE_FAIL_INCOMPLETE);

        $rowKey = $validator->validated()['rowKey'];

        try
        {
            $hashids  = new Hashids(LeaveRequest::HASH_SALT, LeaveRequest::MIN_HASH_LENGTH);
            $leaveId  = $hashids->decode($rowKey)[0];

            $transact = DB::transaction(function () use ($leaveId) 
            {
                // Find the Leave Request record to make sure it exists
                $leave = LeaveRequest::findOrFail($leaveId);

                // If the admin already rejects or approves the leave, 
                // but we are trying to cancel it, unfortunately, we cant.
                $status = $leave->getAttribute(LeaveRequest::f_LeaveStatus);
                error_log("The leave status is $status");
                if ($status != LeaveRequest::LEAVE_STATUS_PENDING)
                    return Extensions::encodeFailMessage('Unable to cancel the leave request as it might have already been rejected or approved');

                // If it does, then remove it.
                $leave->delete();

                return Extensions::encodeSuccessMessage('Leave request successfully cancelled.');
            });

            return $transact;
        }
        catch (ModelNotFoundException $ex) {
            // Handle the error when no employee or leave request record is found
            return Extensions::encodeFailMessage(Messages::MODIFY_FAIL_INEXISTENT);
        } 
        catch (Exception $ex) {
            error_log($ex->getMessage() . ' at ' . $ex->getLine());
            return $failMessage;
        }
    }
}
