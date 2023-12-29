<?php

namespace App\Http\Controllers;

use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $attendanceFields;
    private $employeeFields;

    public function __construct() 
    {
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
            'created_at' ,
        ]);
    }

    public function index()
    {
        return view('backoffice.attendance.index')
            ->with('organizationName', Constants::OrganizationName)
            ->with('ajaxDataSource', route(RouteNames::Attendance['daily']));
    }

    /**
     * Retrieve all attendances that were made today
     */
    public function getDailyAttendances()
    {
        // The current timestamp
        $currentDate = Carbon::now();

        $fields  = array_merge($this->attendanceFields, $this->employeeFields);
        $dataset = DB::table(Attendance::getTableName() . ' as a')
        
        // Instead of whereDate($today), we will use where between

        ->whereBetween('a.created_at', 
        [
            $currentDate->startOfDay()->format(Constants::TimestampFormat), 
            $currentDate->endOfDay()->format(Constants::TimestampFormat)
        ])
        ->select($fields)
        ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('a.created_at', 'desc')
        ->get();

        return json_encode([
            'data' => $dataset->toArray(),
            'icon' => Attendance::getIconClasses()
        ]);
    }
}
