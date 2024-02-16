<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LeaveRequest extends Model
{
    use HasFactory;

    public const f_Emp_FK_ID    = 'emp_fk_id';
    public const f_StartDate    = 'start_date';
    public const f_EndDate      = 'end_date';
    public const f_Duration     = 'duration';
    public const f_LeaveType    = 'leave_type';
    public const f_LeaveStatus  = 'leave_status';

    public const LEAVE_STATUS_PENDING  = 1;
    public const LEAVE_STATUS_APPROVED = 2;
    public const LEAVE_STATUS_REJECTED = 3;

    public const LEAVE_TYPE_SERVICE_INCENTIVE = 1;
    public const LEAVE_TYPE_SICK              = 2;
    public const LEAVE_TYPE_VACATION          = 3;
    public const LEAVE_TYPE_MATERNITY         = 4;
    public const LEAVE_TYPE_PATERNITY         = 5;
    public const LEAVE_TYPE_SOLO_PARENT       = 6;
    public const LEAVE_TYPE_SPECIAL           = 7;
    public const LEAVE_TYPE_VAWC              = 8;

    public const LEAVE_SICK        = 'Sick Leave';
    public const LEAVE_VACATION    = 'Vacation Leave';
    public const LEAVE_SIL         = 'Service Incentive Leave';
    public const LEAVE_MATERNITY   = 'Maternity Leave';
    public const LEAVE_PATERNITY   = 'Paternity Leave';
    public const LEAVE_SOLO_PARENT = 'Parental Leave for Solo Parents';
    public const LEAVE_SPECIAL     = 'Special Leave Benefit for Women';
    public const LEAVE_VAWC        = 'Violence Against Women Leave';

    public const LEAVE_PENDING     = 'Pending';
    public const LEAVE_APPROVED    = 'Approved';
    public const LEAVE_REJECTED    = 'Rejected';

    public const HASH_SALT = 'FCD61F'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;
    
    public static function getTableName() : string {
        return (new self)->getTable();
    }
    
    protected $guarded = [
        'id'
    ];

    public static function getLeaveTypes($onlyValues = false) : array
    {
        $leaveTypes = [
            self::LEAVE_SICK        => self::LEAVE_TYPE_SICK,
            self::LEAVE_VACATION    => self::LEAVE_TYPE_VACATION,
            self::LEAVE_MATERNITY   => self::LEAVE_TYPE_MATERNITY,
            self::LEAVE_PATERNITY   => self::LEAVE_TYPE_PATERNITY,
            self::LEAVE_SIL         => self::LEAVE_TYPE_SERVICE_INCENTIVE,
            self::LEAVE_SOLO_PARENT => self::LEAVE_TYPE_SOLO_PARENT,
            self::LEAVE_SPECIAL     => self::LEAVE_TYPE_SPECIAL,
            self::LEAVE_VAWC        => self::LEAVE_TYPE_VAWC,
        ];

        if ($onlyValues)
            return array_values($leaveTypes);

        return $leaveTypes;
    }

    public static function getLeaveStatuses($onlyValues = false) : array
    {
        $leaveStatuses = [
            self::LEAVE_PENDING  => self::LEAVE_STATUS_PENDING,
            self::LEAVE_APPROVED => self::LEAVE_STATUS_APPROVED,
            self::LEAVE_REJECTED => self::LEAVE_STATUS_REJECTED,
        ];

        if ($onlyValues)
            return array_values($leaveStatuses);

        return $leaveStatuses;
    }

    public function getLeaveRequests(Request $request)
    {
        if (!$request->filled('monthIndex'))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $monthIndex = $request->input('monthIndex');

        // Build the query with role filter applied
        $dataset = $this->buildQuery()->whereMonth('a.created_at', '=', $monthIndex);

        $this->applyRoleFilter($request, $dataset);
        $this->applyStatusFilter($request, $dataset);
        $this->applyLeaveFilter($request, $dataset);

        // Execute the query then expect results
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset, self::HASH_SALT, self::MIN_HASH_LENGTH);

        $monthName = Carbon::createFromFormat('!m', $monthIndex)->monthName;

        return $this->encodeData($request, $dataset, "Month of $monthName");
    }

    public static function findLeaveRequest($rowId)
    {
        $leaveTypeMapping   = Extensions::mapCaseWhen(array_flip(self::getLeaveTypes()),    'l.' . self::f_LeaveType, 'type');
        $leaveStatusMapping = Extensions::mapCaseWhen(array_flip(self::getLeaveStatuses()), 'l.' . self::f_LeaveStatus, 'status');

        $leaveReqFields = Extensions::prefixArray('l.', [
            'id',
            LeaveRequest::f_StartDate   . ' as start',
            LeaveRequest::f_EndDate     . ' as end',
            LeaveRequest::f_Duration    . ' as duration',
        ]);

        $select = array_merge($leaveReqFields, [ 
            Employee::getConcatNameDbRaw('e', 'empname', Constants::NAME_STYLE_EASTERN),
            DB::raw($leaveTypeMapping),
            DB::raw($leaveStatusMapping)
        ]);
        
        $dataset = DB::table(self::getTableName() . ' as l')
                ->select($select)
                ->where('l.id', $rowId)
                ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'l.'.self::f_Emp_FK_ID)
                ->first();

        return $dataset ? (array) $dataset : [];
    }

    private function applyRoleFilter(Request &$request, &$dataset)
    {
        if (
            !empty($request->input('role')) && 
            ($request->input('role') != Constants::RECORD_FILTER_ALL)
        )
        {
            $role = $request->input('role');
            
            // if the filter supplied is invalid, just select all records instead
            if (!in_array($role, Employee::getRoles()))
                $role = Constants::RECORD_FILTER_ALL;

            $dataset->where('e.'.Employee::f_Position, '=', $role);
        }
    }
    
    private function applyStatusFilter(Request &$request, &$dataset)
    {
        if (
            !empty($request->input('status')) && 
            ($request->input('status') != Constants::RECORD_FILTER_ALL)
        )
        {
            $status = $request->input('status');

            if (!in_array($status, array_values( self::getLeaveStatuses() )))
                $status = Constants::RECORD_FILTER_ALL;

            $dataset->where('a.' . self::f_LeaveStatus, '=', $status);
        }
    }

    private function applyLeaveFilter(Request &$request, &$dataset)
    {
        if (
            !empty($request->input('type')) && 
            ($request->input('type') != Constants::RECORD_FILTER_ALL)
        )
        {
            $type = $request->input('type');

            if (!in_array($type, array_values( self::getLeaveTypes() )))
                $type = Constants::RECORD_FILTER_ALL;

            $dataset->where('a.' . self::f_LeaveType, '=', $type);
        }
    }

    /**
    * Base query builder for retrieving leave reqyests
    */
    private function buildQuery()
    {
        $leaveTypeMapping   = Extensions::mapCaseWhen(array_flip($this->getLeaveTypes()),    'a.' . self::f_LeaveType, 'type');
        $leaveStatusMapping = Extensions::mapCaseWhen(array_flip($this->getLeaveStatuses()), 'a.' . self::f_LeaveStatus, 'status');

        $employeeFields = [
            'e.' . Employee::f_EmpNo . ' as idNo',
            Employee::getConcatNameDbRaw('e', 'empname', Constants::NAME_STYLE_EASTERN)
        ];

        $leaveReqFields = Extensions::prefixArray('a.', [
            'id',
            LeaveRequest::f_StartDate   . ' as start',
            LeaveRequest::f_EndDate     . ' as end',
            LeaveRequest::f_Duration    . ' as duration',
        ]);

        $fields = array_merge($leaveReqFields, $employeeFields, [ 
            DB::raw($leaveTypeMapping),
            DB::raw($leaveStatusMapping)
        ]);
        
        $query = DB::table(LeaveRequest::getTableName() . ' as a')
                ->select($fields)
                ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.LeaveRequest::f_Emp_FK_ID)
                ->orderBy('a.created_at', 'desc');

        return $query;
    }

    private function encodeData(Request &$request, $dataset, $descriptiveRange = null)
    {
        $filters = [
            'select_range' => $request->input('range')
        ];

        if ($request->has('monthIndex'))
            $filters['month_index'] = $request->input('monthIndex');

        if ($request->has('role'))
            $filters['select_role'] = $request->input('role');
        else
            $filters['select_role'] = Constants::RECORD_FILTER_ALL;

        return json_encode([
            'data'      => $dataset->toArray(),
            'range'     => $descriptiveRange,
            'filters'   => $filters,
            //'icon'      => Attendance::getIconClasses()
        ]);
    }

    public static function dissolve($recordId)
    {
        try 
        {
            // Make sure that the employee exists
            // $employee = Employee::where(Employee::f_EmpNo, '=', $employeeId)->firstOrFail();
            // $empId = $employee->id;

            $delete = DB::transaction(function () use ($recordId) 
            {
                // Delete the leave request
                $rowsDeleted = LeaveRequest::where('id', '=', $recordId)->delete();

                if ($rowsDeleted > 0) 
                {
                    // To do later:
                    // update employee status from leave to on-duty

                    // but first, check if there are existing leave requests that had
                    // not passed yet. If there are, but the current date is within the 
                    // leave range, then do not update the employee status.
                    // Otherwise, check again if there are leave requests.
                    // If there are leave requests found, compare them to the
                    // current date. If the current date is within the leave request,
                    // do not update
                    return Extensions::encodeSuccessMessage(Messages::GENERIC_DELETE_OK);
                } 
                else 
                {
                    return Extensions::encodeFailMessage(Messages::MODIFY_FAIL_INEXISTENT);
                }
            });

            return $delete;
        } 
        // catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
        //     // Handle the error when no employee is found
        //     return Extensions::encodeFailMessage('Employee not found');
        // }
        catch (\Exception $ex) {
            return Extensions::encodeFailMessage(Messages::GENERIC_DELETE_FAIL);
        }
    }

    /**
     * Approves or Rejects a leave request.
     * 
     * @param string $action - The type of action to perform [ Approve | Reject ]
     */
    public static function completeLeaveRequest($action, Request $request)
    {
        $leaveStatuses = [
            '0'  => LeaveRequest::LEAVE_STATUS_APPROVED,
            '-1' => LeaveRequest::LEAVE_STATUS_REJECTED
        ];

        $successMessages = [
            '0'  => Messages::LEAVE_REQUEST_APPROVED,
            '-1' => Messages::LEAVE_REQUEST_REJECTED
        ];

        $failure = Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $validator   = Validator::make($request->all(), [
            'rowKey' => 'required|string',
        ]);

        if ($validator->fails())
            return Extensions::encodeFailMessage(Messages::UPDATE_FAIL_INCOMPLETE);

        $input_row_key = $validator->validated()['rowKey'];

        try 
        {
            $hashids  = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
            $rowId    = $hashids->decode($input_row_key)[0];

            $transact = DB::transaction(function () use ($rowId, $action, $leaveStatuses) {

                // Find the Leave Request record
                $leave = LeaveRequest::findOrFail($rowId);

                // Find the employee associated with that leave request
                $empId = $leave->toArray()[LeaveRequest::f_Emp_FK_ID];

                // Update the leave request status. Select a status using $action
                $updateLeave = $leave->update([LeaveRequest::f_LeaveStatus => $leaveStatuses[$action]]);

                if (!$updateLeave)
                    throw new Exception;

                // Update the employee status to 'On Leave' only if 
                // the leave request was approved
                if ($leave->wasChanged() && $action == '0') 
                {
                    $emp_affectedRows = Employee::where('id', $empId)->update([
                        Employee::f_Status => Employee::ON_STATUS_LEAVE
                    ]);

                    if (!$emp_affectedRows)
                        throw new ModelNotFoundException;
                }

                return true;
            });

            if ($transact)
            {
                $extraData = [
                    'newStatus' => array_flip(self::getLeaveStatuses())[ $leaveStatuses[$action] ],
                    'rowKey'    => $input_row_key
                ];
                return Extensions::encodeSuccessMessage( $successMessages[$action], $extraData);
            }
            
            return $failure;
        } 
        catch (ModelNotFoundException $ex) {
            // Handle the error when no employee or leave request record is found
            return Extensions::encodeFailMessage(Messages::UPDATE_FAIL_NON_EXISTENT_RECORD);
        } 
        catch (Exception $ex) {
            error_log($ex->getMessage());
            return $failure;
        }
    }
}
