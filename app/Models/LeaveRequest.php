<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Models\Traits\LeaveRequestsAudit;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use OwenIt\Auditing\Contracts\Auditable;

class LeaveRequest extends Model implements Auditable
{
    use HasFactory;
    use LeaveRequestsAudit;
    use \OwenIt\Auditing\Auditable;

    /**
     * {@inheritdoc}
     */
    public function transformAudit(array $data): array
    {
        $this->beautifyTransforms($data);
        
        return $data;
    }

    public const f_Emp_FK_ID    = 'emp_fk_id';
    public const f_StartDate    = 'start_date';
    public const f_EndDate      = 'end_date';
    public const f_Duration     = 'duration';
    public const f_LeaveType    = 'leave_type';
    public const f_LeaveStatus  = 'leave_status';

    public const LEAVE_STATUS_PENDING   = 1;
    public const LEAVE_STATUS_APPROVED  = 2;
    public const LEAVE_STATUS_REJECTED  = 3;
    public const LEAVE_STATUS_UNNOTICED = 4;

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
    public const LEAVE_UNNOTICED   = 'Unnoticed';

    public const HASH_SALT = 'FCD61F'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;
    
    // Need to place this here for Object relational mapping
    protected $table = Constants::TABLE_LEAVE_REQUESTS;

    // For other uses
    public static function getTableName() : string {
        return Constants::TABLE_LEAVE_REQUESTS;
    }

    // This is the friendly name that will be used when 
    // presenting this model in the Audits table.
    public static function getFriendlyName() : string {
        return 'Leave Request';
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

    public static function getTypes($onlyValues = false) : array
    {
        $leaveTypes = [
            self::LEAVE_TYPE_SICK               => self::LEAVE_SICK        ,
            self::LEAVE_TYPE_VACATION           => self::LEAVE_VACATION    ,
            self::LEAVE_TYPE_MATERNITY          => self::LEAVE_MATERNITY   ,
            self::LEAVE_TYPE_PATERNITY          => self::LEAVE_PATERNITY   ,
            self::LEAVE_TYPE_SERVICE_INCENTIVE  => self::LEAVE_SIL         ,
            self::LEAVE_TYPE_SOLO_PARENT        => self::LEAVE_SOLO_PARENT ,
            self::LEAVE_TYPE_SPECIAL            => self::LEAVE_SPECIAL     ,
            self::LEAVE_TYPE_VAWC               => self::LEAVE_VAWC        ,
        ];

        if ($onlyValues)
            return array_values($leaveTypes);

        return $leaveTypes;
    }

    public static function getLeaveStatuses($onlyValues = false) : array
    {
        $leaveStatuses = [
            self::LEAVE_PENDING     => self::LEAVE_STATUS_PENDING,
            self::LEAVE_APPROVED    => self::LEAVE_STATUS_APPROVED,
            self::LEAVE_REJECTED    => self::LEAVE_STATUS_REJECTED,
            self::LEAVE_UNNOTICED   => self::LEAVE_STATUS_UNNOTICED
        ];

        if ($onlyValues)
            return array_values($leaveStatuses);

        return $leaveStatuses;
    }

    public static function getStatuses($onlyValues = false) : array
    {
        $leaveStatuses = [
            self::LEAVE_STATUS_PENDING      => self::LEAVE_PENDING,
            self::LEAVE_STATUS_APPROVED     => self::LEAVE_APPROVED,
            self::LEAVE_STATUS_REJECTED     => self::LEAVE_REJECTED,
            self::LEAVE_STATUS_UNNOTICED    => self::LEAVE_UNNOTICED
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
        error_log($dataset->toSql());
        // Execute the query then expect results
        $dataset = $dataset->get();

        //Extensions::hashRowIds($dataset, self::HASH_SALT, self::MIN_HASH_LENGTH);
        $this->sanitizeResults($dataset);

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

            $dataset->where('e.'.Employee::f_Role, '=', $role);
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
            // Find the target leave request record and make sure it exists then,
            // Find the employee associated with that record. Make sure they exist too.
            $leaveReq = LeaveRequest::where('id', $recordId)->firstOrFail();
            $employee = Employee::findOrFail( $leaveReq->getAttribute( self::f_Emp_FK_ID ) );

            // Delete the leave request as it is no longer needed
            $leaveReq->deleteOrFail();

            // Find the other leave requests tied to that employee. We select only the Approved ones
            $leaveExists = LeaveRequest
                         ::where(self::f_Emp_FK_ID,   '=',  $employee->id)
                         ->where(self::f_LeaveStatus, '=',  self::LEAVE_STATUS_APPROVED)
                         ->where(self::f_StartDate,   '<=', now())
                         ->where(self::f_EndDate,     '>=', now())
                         ->exists();

            $empStatus = $employee->getAttribute( Employee::f_Status );

            if ($leaveExists && $empStatus != Employee::ON_STATUS_LEAVE)
            {
                $employee->setAttribute(Employee::f_Status, Employee::ON_STATUS_LEAVE);
                $employee->saveOrFail();
            }
            else if (!$leaveExists && $empStatus != Employee::ON_STATUS_ACTIVE)
            {
                $employee->setAttribute(Employee::f_Status, Employee::ON_STATUS_ACTIVE);
                $employee->saveOrFail();
            }

            return Extensions::encodeSuccessMessage(Messages::GENERIC_DELETE_OK);
        } 
        catch (ModelNotFoundException $ex) {
            // When no records of leave or employee were found
            return Extensions::encodeFailMessage(Messages::MODIFY_FAIL_INEXISTENT);
        }
        catch (QueryException $ex) {
            // We assume saveOrFail to throw errors here
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        }
        catch (\Exception $ex) {
            // Common errors
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
        $actionMap = 
        [
            'approve' => [
                'success' => Messages::LEAVE_REQUEST_APPROVED,
                'value'   => self::LEAVE_STATUS_APPROVED
            ],

            'reject'  => [
                'success' => Messages::LEAVE_REQUEST_REJECTED,
                'value'   => self::LEAVE_STATUS_REJECTED
            ],
        ];

        $failMessage = Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);
        $validator   = Validator::make($request->all(), [ 'rowKey' => 'required|string' ]);

        if ($validator->fails())
            return Extensions::encodeFailMessage(Messages::UPDATE_FAIL_INCOMPLETE);

        $rowKey = $validator->validated()['rowKey'];

        try
        {
            $hashids  = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
            $leaveId  = $hashids->decode($rowKey)[0];

            $transact = DB::transaction(function () use ($leaveId, $action, $actionMap, $rowKey) 
            {
                //========= TASK 1 :: UPDATE LEAVE REQUEST =========//
                
                // Find the Leave Request record to make sure it exists
                $leave = LeaveRequest::findOrFail($leaveId);

                // Update the leave request status. Select a status by $action
                $newLeaveStatus = $actionMap[$action]['value'];

                $leave->setAttribute(self::f_LeaveStatus, $newLeaveStatus);

                if (!($leave->save()))
                    throw new Exception;

                // We assume the operation succeeds and 
                // we can now use the updated information
                $leave = $leave->toArray();

                $startDate = Carbon::parse($leave[self::f_StartDate]);
                $endDate   = Carbon::parse($leave[self::f_EndDate]);

                //========= TASK 2 :: UPDATE EMPLOYEE STATUS =========//

                // Find the employee's status associated with that leave request
                $empId = $leave[self::f_Emp_FK_ID];

                $currentEmpStatus = Employee::where('id', $empId)->value(Employee::f_Status);
                $newEmpStatus     = Employee::ON_STATUS_ACTIVE;

                if ( $leave[self::f_LeaveStatus] == self::LEAVE_STATUS_APPROVED &&
                     now()->startOfDay()->between($startDate, $endDate)) 
                {
                    $newEmpStatus = Employee::ON_STATUS_LEAVE;
                }
                
                // Update the employee status to 'On Leave' only if the leave request was approved
                // and only if the current date is within the range of the leave
                // and if its current status is not the same as the new status.
                if ($currentEmpStatus != $newEmpStatus)
                {
                    $empStatus = DB::table(Employee::getTableName())
                        ->where('id', $empId)
                        ->update([Employee::f_Status => $newEmpStatus]);
                 
                    // If the $updateStatus failed, we revert the changes
                    if ($empStatus < 1)
                        throw new ModelNotFoundException;
                }

                return [
                    'newStatus' => array_flip(self::getLeaveStatuses())[ $newLeaveStatus ],
                    'rowKey'    => $rowKey
                ];
            });

            return Extensions::encodeSuccessMessage($actionMap[$action]['success'], $transact);
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

    private function sanitizeResults($dataset)
    {
        $hashids = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
        
        foreach ($dataset as $data) 
        {
            if ($data->id)
                $data->id = $hashids->encode($data->id);

            if ($data->empname)
                $data->empname = ucwords($data->empname);
        }
    }

    // Object Relational Mapping; Each leave requests belongs to an employee
    public function employee() 
    {
        return $this->belongsTo(\App\Models\Employee::class, self::f_Emp_FK_ID);
    }
}
