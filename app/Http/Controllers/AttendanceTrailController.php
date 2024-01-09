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
use Illuminate\Support\Facades\DB;

class AttendanceTrailController extends Controller
{
    private $emp_hashids;
    private $fullForm;
    private $shortForm;

    public function __construct() 
    {
        $this->emp_hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);

        $this->fullForm  = ['Hr', 'Mins', 'Secs'];
        $this->shortForm = ['h', 'm', 's'];
    }

    public function index(Request $request)
    {
        if (empty($request->input('employee-key')))
            return back();

        $key = $request->input('employee-key');
        $id = $this->emp_hashids->decode($key);

        $empDetails = Employee::where('id', '=', $id[0])
            ->select([
                Employee::f_FirstName   . ' as fname',
                Employee::f_MiddleName  . ' as mname',
                Employee::f_LastName    . ' as lname',
                Employee::f_EmpNo       . ' as idNo'
            ])
            ->first();

        if (!$empDetails)
            return back();

        $routes = [
            'trails_all' => route(RouteNames::Trails['all']),
        ];

        $employee = $empDetails->toArray();

        return view('backoffice.teachers.trails')
            ->with('routes',        $routes)
            ->with('empKey',        $request->input('employee-key'))
            ->with('empName',       implode(' ', [ $employee['fname'], $employee['mname'], $employee['lname'], ]))
            ->with('empIdNo',       $employee['idNo']);
    }

    public function getTrails(Request $request)
    {
        try
        {
            $key = $request->input('employee-key');
            $id = $this->emp_hashids->decode($key);

            $dataset = DB::table(Attendance::getTableName())
            ->where(Attendance::f_Emp_FK_ID, '=', $id[0])
            ->select([
                DB::raw('DATE_FORMAT(' . Attendance::f_TimeIn . ', "%l:%i %p") as am_in'),
                DB::raw('DATE_FORMAT(' . Attendance::f_LunchStart . ', "%l:%i %p") as am_out'),
                DB::raw('DATE_FORMAT(' . Attendance::f_LunchEnd . ', "%l:%i %p") as pm_in'),
                DB::raw('DATE_FORMAT(' . Attendance::f_TimeOut . ', "%l:%i %p") as pm_out'),
                Attendance::f_Duration    . ' as duration',
                Attendance::f_Late        . ' as late',
                Attendance::f_UnderTime   . ' as undertime',
                Attendance::f_OverTime    . ' as overtime',
                Attendance::f_Status      . ' as status',
                'created_at',
                DB::raw('DAY(created_at) as day_number'),
                DB::raw('DATE_FORMAT(created_at, "%a") as day_name')
            ])
            ->get()
            ->toArray();

            // Convert the time duration format.
            // Replace the full form into shorter form
            foreach ($dataset as &$data) 
            {
                $data->duration  = $this->formatDuration($data->duration);
                $data->late      = $this->formatDuration($data->late);
                $data->undertime = $this->formatDuration($data->undertime);
                $data->overtime  = $this->formatDuration($data->overtime);
            }

            unset($data); // unset reference to last element

            return json_encode([
                'data' => $dataset,
                //'icon' => Attendance::getIconClasses()
            ]);
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    /**
     * Fix the time duration formatting. 
     * If an hour exists, remove its seconds.
     * This applies to the duration, late, over and undertime
     */
    function formatDuration($duration)
    {
        $duration = str_replace($this->fullForm, $this->shortForm, $duration);

        if (strpos($duration, 'h') !== false) {
            $parts = explode(' ', $duration);
            $duration = $parts[0] . ' ' . $parts[1];
        }
        return $duration;
    }
}

// https://www.youtube.com/watch?v=zb-UuuRK974