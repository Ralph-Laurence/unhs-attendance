<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\RouteNames;
use App\Http\Utils\ValidationMessages;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScannerController extends Controller
{
    public function index()
    {
        $layoutTitles = [
            'system'    => Constants::SystemName,
            'header'    => Constants::OrganizationName,
            'footer'    => date('Y') ." ". Constants::OrganizationName,
            'version'   => Constants::BuildVersion
        ];

        $routes = [
            'recordsManagement' => route(RouteNames::Attendance['index']),
            'scannerPostURL'    => route(RouteNames::Scanner['decode']),
            'scannerHistory'    => route(RouteNames::Scanner['history']),
            'pincodeForm'       => route(RouteNames::Scanner['authpin'])
        ];

        return view('scanner.index')
            ->with('layoutTitles', $layoutTitles)
            ->with('routes', $routes);
    }

    public function history()
    {
        // The current date
        $currentDate = Carbon::now();

        $attendanceFields = Extensions::prefixArray('a.', [
            Attendance::f_TimeIn   . ' as timein',
            Attendance::f_TimeOut  . ' as timeout',
            Attendance::f_Duration . ' as duration',
            Attendance::f_Status   . ' as status',
        ]);

        $employeeFields = Extensions::prefixArray('e.', [
            Employee::f_FirstName  . ' as fname',
            Employee::f_MiddleName . ' as mname',
            Employee::f_LastName   . ' as lname',
            Employee::f_Position   . ' as role',
        ]);

        $selectFields = array_merge($attendanceFields, $employeeFields);

        $dataset = DB::table(Attendance::getTableName() . ' as a')
        ->whereBetween('a.created_at', 
        [
            $currentDate->startOfDay()->format(Constants::TimestampFormat), 
            $currentDate->endOfDay()->format(Constants::TimestampFormat)
        ])
        ->where('a.' . Attendance::f_Status, '!=', Attendance::STATUS_ABSENT)
        ->select($selectFields)
        ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('a.created_at', 'desc')
        ->limit(10)
        ->get();

        return json_encode([
            'data'  => $dataset->toArray(),
            'icon' => Attendance::getIconClasses()
        ]);
    }

    // From PIN
    public function authenticatePin(Request $request)
    {
        // Validate the PIN codes first to make sure
        // they are filled and meets the required criteria
        $validationFields = [
            'input-id-no'  => 'required|regex:' . RegexPatterns::NUMERIC_DASH,
            'input-pin-no' => 'required|regex:' . RegexPatterns::NUMERIC
        ];

        $validationMessages = 
        [
            'input-id-no.required'  => 'Please enter your ID Number',
            'input-id-no.regex'     => ValidationMessages::invalid('ID Number'),

            'input-pin-no.required' => 'Please enter your PIN Code',
            'input-pin-no.regex'    => ValidationMessages::invalid('PIN Code'),
        ];

        $validator = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator->fails())
            return json_encode(['validation_stat' => Constants::ValidationStat_Failed] + 
            [
                'errors'  => $validator->errors(),
                'err_msg' => Messages::ATTENDANCE_CRED_REQUIRE //Messages::ATTENDANCE_CRED_FAIL
            ]);

        $inputs = $validator->validated();

        // We assume successful validation. 
        // Find the employee with that matching credentials
        $employee = Employee::select(['id', Employee::f_PINCode . ' as pin'])
                  ->where(Employee::f_EmpNo,   '=', $inputs['input-id-no'])
                  ->first();

        $errMessage = Extensions::encodeFailMessage(Messages::ATTENDANCE_CRED_FAIL, Constants::RecordNotFound);

        if (!$employee)
            return $errMessage;

        $emp = $employee->toArray();
        $pin = decrypt($emp['pin']);

        if ($pin != $inputs['input-pin-no'])
            return $errMessage;

        error_log(print_r($emp, true));
        
        return $this->handleAttendance($emp['id']);
    }

    // Process Attendance From Scanner
    public function decode(Request $request)
    {
        // Read QR code data
        $hash    = $request->input('hash');
        $hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
        $decode  = $hashids->decode($hash);

        // Make sure that it has contents
        if (is_null($decode) || empty($decode))
            return Extensions::encodeFailMessage( Messages::QR_CODE_UNREADABLE);

        // Check first if the employee id exists
        // then begin processing their atendance
        if (!Employee::where('id', $decode[0])->exists())
            return Extensions::encodeFailMessage(Messages::QR_CODE_NOT_RECOGNIZED);

        return $this->handleAttendance($decode[0]);
    }

    public function handleAttendance(int $empId)
    {
        // Handle Attendance Wont Work when the 
        // employee has been marked as Absent

        // Tasks to be added:
        // A1 -> Prevent attendances 1hr before dismissal or after dismissal
        // A2 -> Exclude Sundays and Holidays (Dont allow scans during those days)

        // The current date
        $currentDate = Carbon::now();
    
        // Start a transaction to ensure data consistency
        $data = DB::transaction(function () use ($empId, $currentDate) 
        {
            // Check if there's an existing attendance record for the employee today
            $attendance = Attendance::whereBetween('created_at', [
                    $currentDate->startOfDay()->format(Constants::TimestampFormat), 
                    $currentDate->endOfDay()->format(Constants::TimestampFormat)
                ])
                ->where(Attendance::f_Emp_FK_ID, $empId)
                ->first();

            // If there's no existing record, insert a new one
            if (!$attendance) 
            {
                // implement task A1 here ____

                return $this->insertNewAttendance($empId);
            } 
            // If there's an existing record, update it
            else 
            {
                // Process Lunch Start only if there is an existing Time In 
                if ($attendance->time_in && !$attendance->lunch_start) 
                    return $this->updateLunchStart($attendance);

                // Process Lunch End only if there is an existing Lunch Start
                else if ($attendance->lunch_start && !$attendance->lunch_end) 
                    return $this->updateLunchEnd($attendance);

                // Process the Time out only if there were Time In, Lunch Start and Lunch End
                else if ($attendance->lunch_end && !$attendance->time_out) 
                    return $this->updateTimeOut($attendance);
            }
        });

        return $data;
    }
    
    private function insertNewAttendance($empId) : string
    {
        $insertAttendance = Attendance::createTimeIn($empId);

        if ($insertAttendance)
        {
            $empDetails = DB::table(Employee::getTableName())
                        ->select(Employee::f_FirstName . ' as fname', Employee::f_LastName . ' as lname', Employee::f_Position . ' as role')
                        ->where('id', $empId)
                        ->first();

            return Extensions::encodeSuccessMessage('Have a nice day!', [
                'name'      => implode(' ', [$empDetails->fname, $empDetails->lname]),
                'role'      => Employee::RoleToString[$empDetails->role],
                'timeIn'    => $insertAttendance->time_in->format( Constants::BasicTimeFormat )
            ]);
        }

        return null;
    }

    private function updateLunchStart(Attendance $attendance)
    {
        $lunchStart = Carbon::now();
        $status     = Attendance::STATUS_BREAK;

        $attendance->update([
            Attendance::f_LunchStart => $lunchStart,
            Attendance::f_Status     => $status,
        ]);

        // Check if lunch started after 12:10 PM and add to overtime if so
        $lunchStartOffset = Carbon::createFromTimeString(Attendance::$lunchOverTime);

        if ($lunchStart->gt($lunchStartOffset)) 
        {
            $overtime = $lunchStart->diffInSeconds($lunchStartOffset);
            $attendance->increment(Attendance::f_OverTime, $overtime);
        }

        return Extensions::encodeSuccessMessage('Out for lunch...', ['status'  => $status]);
    }

    private function updateLunchEnd(Attendance $attendance)
    {
        $status = Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_LunchEnd  => Carbon::now(),
            Attendance::f_Status    => $status,
        ]);

        return Extensions::encodeSuccessMessage('Welcome Back!', ['status'  => $status]);
    }
    
    private function updateTimeOut(Attendance $attendance)
    {
        // Set time_out first
        $timeOut = Carbon::now();

        // Then calculate the duration, undertime and so on
        $workHours  = Carbon::parse($attendance->time_in)->diffInSeconds($timeOut) / 3600;
        $lunchHours = Carbon::parse($attendance->lunch_start)->diffInSeconds(Carbon::parse($attendance->lunch_end)) / 3600;
        $duration   = $workHours - $lunchHours;

        // Calculate undertime based on early dismissal time
        $earlyDismissal = Carbon::parse(Attendance::$earlyDismissal);
        $undertime  = $timeOut->lt($earlyDismissal) ? $earlyDismissal->diffInSeconds($timeOut) / 3600 : 0;
        $overtime   = $workHours > 8 ? $workHours - 8 : 0;

        // Check if TimeOut is before dismissal time 4:50 PM
        // $status = $timeOut->lt(Carbon::parse(Attendance::$earlyDismissal)) ? 
        //           Attendance::STATUS_UNDERTIME : 
        //           Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_TimeOut   => $timeOut,
            Attendance::f_Status    => Attendance::STATUS_PRESENT, //$status,
            Attendance::f_Duration  => Attendance::formatTimeDuration($duration),
            Attendance::f_UnderTime => Attendance::formatTimeDuration($undertime),
            Attendance::f_OverTime  => Attendance::formatTimeDuration($overtime),
        ]);

        return Extensions::encodeSuccessMessage('Good Bye!');//, ['status' => $status]);
    }
}

/**
* The total duration can be calculated by subtracting the time spent on lunch from the 
* total time spent at work. Here’s how:

timestamp [
    'clockin'     => '2024-01-21 17:18:16', // 5:18pm
    'lunch_start' => '2024-01-21 17:19:49', // 5:19pm
    'lunch_end'   => '2024-01-21 23:32:25', 
    'clock_out'   => '2024-01-21 23:32:34'
]

Calculate the total time spent at work: clock_out - clockin
Calculate the total time spent on lunch: lunch_end - lunch_start
Subtract the lunch time from the total work time to get the total duration.
Let’s calculate:

Total time at work: 23:32:34 - 17:18:16 = 6 hours, 14 minutes and 18 seconds
Total time at lunch: 23:32:25 - 17:19:49 = 6 hours, 12 minutes and 36 seconds
Total duration: 6 hours, 14 minutes and 18 seconds - 6 hours, 12 minutes and 36 seconds = 1 minute and 42 seconds
So, the total duration is 1 minute and 42 seconds. Please note that this is a very short duration for a workday, 
and the timestamps for lunch seem to be incorrect as they indicate that lunch ended after clocking out. 
Please check the timestamps again.
*/