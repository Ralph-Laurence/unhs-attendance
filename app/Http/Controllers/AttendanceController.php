<?php

namespace App\Http\Controllers;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $attendanceFields;
    private $employeeFields;

    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids();

        $this->employeeFields = Extensions::prefixArray('e.', [
            Employee::f_FirstName  . ' as fname',
            Employee::f_MiddleName . ' as mname',
            Employee::f_LastName   . ' as lname',
            Employee::f_Position   . ' as role',
        ]);

        $this->attendanceFields = Extensions::prefixArray('a.', [
            Attendance::f_TimeIn   . ' as timein',
            Attendance::f_TimeOut  . ' as timeout',
            Attendance::f_Duration . ' as duration',
            Attendance::f_Status   . ' as status',
            'id',
            'created_at' ,
        ]);
    }

    public function index()
    {
        $routes = [
            'filter_thisWeek' => route(RouteNames::Attendance['weekly']),
            'filter_thisDay'  => route(RouteNames::Attendance['daily']),
            'deleteRoute'     => route(RouteNames::Attendance['delete']),
            'scannerRoute'    => route(RouteNames::Scanner['index'])
        ];

        $roleFilters = [];

        foreach (Employee::RoleToString as $k => $v) 
        {
            $hashKey = $this->hashids->encode($k);
            $roleFilters[$hashKey] = $v;
        }

        return view('backoffice.attendance.index')
            ->with('routes'             , $routes)
            ->with('roleFilters'        , $roleFilters);
    }

    public function destroy(Request $request) 
    {
        $key = $request->input('rowKey');
        $failMessage = Extensions::encodeFailMessage(Messages::ATTENDANCE_DELETE_FAIL);

        try 
        {
            $hash = $this->hashids->decode($key);
            $rowsDeleted = Attendance::where('id', '=', $hash)->delete();

            if ($rowsDeleted > 0)
                return Extensions::encodeSuccessMessage(Messages::ATTENDANCE_DELETE_OK);
            else
                return $failMessage;
        }
        catch (Exception $ex) 
        {
            return $failMessage;
        }
    }

    /**
     * Retrieve all attendances that were made today
     */
    public function getDailyAttendances($employeeType = null)
    {
        // The current timestamp
        $currentDate = Carbon::now();

        // Instead of whereDate($today), we will use where between
        $dataset = $this->buildAttendanceQuery()
            ->whereBetween('a.created_at', 
            [
                $currentDate->startOfDay()->format(Constants::TimestampFormat), 
                $currentDate->endOfDay()->format(Constants::TimestampFormat)
            ]);

        if ($employeeType)
                $dataset->where('e.role', '=', $employeeType);

        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);
        
        return $this->makeAttendanceData($dataset);
    }

    /**
     * Retrieve all attendances this week
     */
    public function getWeeklyAttendances()
    {
        $currentWeek = Extensions::getCurrentWeek();

        $dataset = $this->buildAttendanceQuery()
            ->where('a.' . Attendance::f_WeekNo, '=', $currentWeek)
            ->get();

        Extensions::hashRowIds($dataset);

        return $this->makeAttendanceData($dataset);
    }

    public function autoAbsentEmployees() 
    {
        Attendance::autoAbsentEmployees();
    }

    /**
    * Base query builder for retrieving attendances 
    */
    private function buildAttendanceQuery()
    {
        $fields  = array_merge($this->attendanceFields, $this->employeeFields);
        $query = DB::table(Attendance::getTableName() . ' as a')
        ->select($fields)
        ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('a.created_at', 'desc');

        return $query;
    }

    /**
     * Encode the datasets into JSON that will be sent as AJAX response
     */
    private function makeAttendanceData($dataset)
    {
        return json_encode([
            'data' => $dataset->toArray(),
            'icon' => Attendance::getIconClasses()
        ]);
    }
}


/**
$user = User::find(1);

if ($user->trashed()) {
    // The user was soft-deleted
}
* In this code, trashed will return true if the user was soft-deleted, 
* and false otherwise. This can be useful if we’re using soft deletes 
* in our Laravel application. If we’re not using soft deletes, we can 
* ignore this part.
 */

 /**
  * To show all employees including their total ‘late’ timestamps, you can use the following query:
SELECT
	CONCAT(e.firstname,' ', e.middlename,' ', e.lastname) as 'name',
    COUNT(a.late) as 'late_count',
    GROUP_CONCAT(a.late SEPARATOR ', ') as 'late_timestamps'
FROM employees as e
LEFT JOIN attendances as a ON a.emp_fk_id = e.id
GROUP BY e.id```

This query will return the name of each employee, the number of times they were late, and a comma-separated list of all the late timestamps for each employee [^1^][1].

I hope this helps!
  */