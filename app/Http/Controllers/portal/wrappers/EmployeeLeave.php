<?php 

namespace App\Http\Controllers\portal\wrappers;

use App\Http\Utils\Extensions;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeLeave 
{
    public function getLeaveRequests(Request $request)
    {
        $dataset = $this->buildSelectQuery($request->input('requestedOn'))->get();

        foreach($dataset as $row)
        {
            $isPending = ($row->status == LeaveRequest::LEAVE_PENDING) ? 1 : 0;
            $row->isPending = $isPending;

            $row->id = self::hashId($row->id);
        }

        return $this->encodeData($dataset);
    }

    /**
    * Base query builder for retrieving the leave reqyests
    * of the currently logged on Employee
    */
    private function buildSelectQuery($filter = null)
    {
        $model = new LeaveRequest();

        $leaveTypeMapping   = Extensions::mapCaseWhen(array_flip($model->getLeaveTypes()),    'l.'.LeaveRequest::f_LeaveType,   'type');
        $leaveStatusMapping = Extensions::mapCaseWhen(array_flip($model->getLeaveStatuses()), 'l.'.LeaveRequest::f_LeaveStatus, 'status');

        $fields = array_merge(Extensions::prefixArray('l.', [
            'id',
            LeaveRequest::f_Duration . ' as duration',
        ]), 
        [
            DB::raw($leaveTypeMapping),
            DB::raw($leaveStatusMapping),
            DB::raw(Extensions::date_format_bdY_join('l', LeaveRequest::f_StartDate, 'date_from')),
            DB::raw(Extensions::date_format_bdY_join('l', LeaveRequest::f_EndDate  , 'date_to')),
            DB::raw(Extensions::date_format_bdY_join('l', 'created_at'  , 'request_date')),
        ]);
        
        $query = DB::table(LeaveRequest::getTableName() . ' as l')
                ->select($fields)
                ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'l.'.LeaveRequest::f_Emp_FK_ID)
                ->orderBy('l.created_at', 'desc')
                ->where('e.id', '=', Auth::id());

        if (!is_null($filter) && $filter == '1')
        {
            $monthNumber = Carbon::now()->month;      // This month
            $query->whereMonth('l.created_at', '=', $monthNumber);
        }

        return $query;
    }

    private function encodeData($dataset)
    {
        return json_encode([
            'data' => $dataset->toArray()
        ]);
    }

    public static function hashId($id)
    {
        $hashids = new Hashids(LeaveRequest::HASH_SALT, LeaveRequest::MIN_HASH_LENGTH);
        return $hashids->encode($id);
    }
}