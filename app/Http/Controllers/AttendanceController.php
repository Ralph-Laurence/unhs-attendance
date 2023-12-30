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
        return view('backoffice.attendance.index')
            ->with('deleteRoute'        , route(RouteNames::Attendance['delete']))
            ->with('scannerRoute'       , route(RouteNames::Scanner['index']))
            ->with('organizationName'   , Constants::OrganizationName)
            ->with('ajaxDataSource'     , route(RouteNames::Attendance['daily']));
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
    public function getDailyAttendances(Request $request)
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

        $hashids = new Hashids();

        // Loop through the dataset and replace each id with its hashed equivalent
        foreach ($dataset as $data) {
            $data->id = $hashids->encode($data->id);
        }
        
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