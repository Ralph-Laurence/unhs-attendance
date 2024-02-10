<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $roleMapping        = Extensions::mapCaseWhen(Employee::RoleToString,                'e.' . Employee::f_Position, 'role');
        $leaveTypeMapping   = Extensions::mapCaseWhen(array_flip($this->getLeaveTypes()),    'a.' . self::f_LeaveType, 'type');
        $leaveStatusMapping = Extensions::mapCaseWhen(array_flip($this->getLeaveStatuses()), 'a.' . self::f_LeaveStatus, 'status');

        $fname  = Employee::f_FirstName;
        $mname  = Employee::f_MiddleName;
        $lname  = Employee::f_LastName;

        $employeeFields = [
            'e.' . Employee::f_EmpNo      . ' as idNo',
            DB::raw("CONCAT_WS(' ', e.$fname, NULLIF(e.$mname, ''), e.$lname) as empname")
        ];

        $leaveReqFields = Extensions::prefixArray('a.', [
            'id',
            LeaveRequest::f_StartDate   . ' as start',
            LeaveRequest::f_EndDate     . ' as end',
            LeaveRequest::f_Duration    . ' as duration',
        ]);

        $fields = array_merge($leaveReqFields, $employeeFields, [ 
            DB::raw($roleMapping), 
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

                    return Extensions::encodeSuccessMessage(Messages::GENERIC_DELETE_OK);
                } 
                else 
                {
                    return Extensions::encodeFailMessage(Messages::DELETE_FAIL_INEXISTENT);
                }
            });

            return $delete;
        } 
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            // Handle the error when no employee is found
            return Extensions::encodeFailMessage('Employee not found');
        }
        catch (\Exception $ex) {
            return Extensions::encodeFailMessage(Messages::GENERIC_DELETE_FAIL);
        }
    }
}
