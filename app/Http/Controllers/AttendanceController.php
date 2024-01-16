<?php

namespace App\Http\Controllers;

use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private $hashids;

    private $attendanceRanges;

    private const RANGE_TODAY = 'this_day';
    private const RANGE_WEEK  = 'this_week';
    private const RANGE_MONTH = 'by_month';

    public function __construct() 
    {
        $this->hashids = new Hashids();

        $this->attendanceRanges = [
            'today' => self::RANGE_TODAY,
            'week'  => self::RANGE_WEEK,
            'month' => self::RANGE_MONTH
        ];
    }

    public function index()
    {
        $routes = [
            'ajax_get_all' => route(RouteNames::Attendance['get']),
            'deleteRoute'  => route(RouteNames::Attendance['delete']),
            'scannerRoute' => route(RouteNames::Scanner['index'])
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
            $rowsDeleted = Attendance::where('id', '=', $hash[0])->delete();

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
     * Select Attendance Records with the appropriate filters
     */
    public function getAttendances(Request $request)
    {     
        $selectRange = $request->input('range');

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!in_array($selectRange, $this->attendanceRanges, true))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $model = new Attendance;

        $transactions = [
            self::RANGE_TODAY => $model->getDailyAttendances($request),
            self::RANGE_WEEK  => $model->getWeeklyAttendances($request),
            self::RANGE_MONTH => $model->getMonthlyAttendances($request)
        ];

        $dataset = $transactions[$selectRange];
        
        return $dataset;
    }

    /**
     * Executed by a CRON Job
     */
    public function autoAbsentEmployees() 
    {
        Attendance::autoAbsentEmployees();
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