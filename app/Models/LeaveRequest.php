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

    public const LEAVE_STATUS_PENDING  = 0; //'Pending';
    public const LEAVE_STATUS_APPROVED = 1; //'Approved';
    public const LEAVE_STATUS_REJECTED = 2; //'Rejected';

    public const LEAVE_TYPE_SERVICE_INCENTIVE = 1;
    public const LEAVE_TYPE_SICK              = 2;
    public const LEAVE_TYPE_VACATION          = 3;
    public const LEAVE_TYPE_MATERNITY         = 4;
    public const LEAVE_TYPE_PATERNITY         = 5;
    public const LEAVE_TYPE_SOLO_PARENT       = 6;
    public const LEAVE_TYPE_SPECIAL           = 7;
    public const LEAVE_TYPE_VAWC              = 8;

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
            Constants::LEAVE_SICK        => self::LEAVE_TYPE_SICK,
            Constants::LEAVE_VACATION    => self::LEAVE_TYPE_VACATION,
            Constants::LEAVE_MATERNITY   => self::LEAVE_TYPE_MATERNITY,
            Constants::LEAVE_PATERNITY   => self::LEAVE_TYPE_PATERNITY,
            Constants::LEAVE_SIL         => self::LEAVE_TYPE_SERVICE_INCENTIVE,
            Constants::LEAVE_SOLO_PARENT => self::LEAVE_TYPE_SOLO_PARENT,
            Constants::LEAVE_SPECIAL     => self::LEAVE_TYPE_SPECIAL,
            Constants::LEAVE_VAWC        => self::LEAVE_TYPE_VAWC,
        ];

        if ($onlyValues)
            return array_values($leaveTypes);

        return $leaveTypes;
    }

    public static function getLeaveStatuses()
    {
        return [
            Constants::LEAVE_PENDING  => 0,
            Constants::LEAVE_APPROVED => 1,
            Constants::LEAVE_REJECTED => 2,
        ];
    }

    public function getLeaveRequests(Request $request)
    {
        if (!$request->filled('monthIndex'))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $monthIndex = $request->input('monthIndex');

        // Build the query with role filter applied
        $dataset = $this->buildQuery()->whereMonth('a.created_at', '=', $monthIndex);

        $this->applyRoleFilter($request, $dataset);

        // Execute the query then expect results
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);

        $monthName = Carbon::createFromFormat('!m', $monthIndex)->monthName;

        return $this->encodeData($request, $dataset, "Month of $monthName");
    }

    private function applyRoleFilter(Request &$request, &$dataset)
    {
        if (
            ($request->has('role') && $request->filled('role')) && 
            ($request->input('role') != Constants::RECORD_FILTER_ALL)
        )
        {
            $role  = $request->input('role');
            $roles = Employee::RoleToString; 
            
            if (!in_array($role, $roles))
            {
                $request->replace(['role' => Constants::RECORD_FILTER_ALL]);
                return;
            }

            $dataset->where('e.'.Employee::f_Position, '=', array_flip($roles)[ $role ]);
        }
    }

    /**
    * Base query builder for retrieving leave reqyests
    */
    private function buildQuery()
    {
        $roleMapping      = Extensions::mapCaseWhen(Employee::RoleToString,             'e.' . Employee::f_Position, 'role');
        $leaveTypeMapping = Extensions::mapCaseWhen(array_flip($this->getLeaveTypes()), 'a.' . self::f_LeaveType, 'type');

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
            LeaveRequest::f_LeaveStatus . ' as status'
        ]);

        $fields = array_merge([ DB::raw($roleMapping), DB::raw($leaveTypeMapping) ], $leaveReqFields, $employeeFields);
        
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
}
