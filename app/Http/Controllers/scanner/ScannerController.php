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
use App\Models\Constants\StaffConstants;
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
            Attendance::f_Status   . ' as status',
        ]);

        $attendanceFields[] = Attendance::timeStringToDurationRaw(Attendance::f_Duration, 'a');

        $fname = Employee::f_FirstName;
        $mname = Employee::f_MiddleName;
        $lname = Employee::f_LastName;

        $employeeFields = [
            DB::raw("CONCAT_WS(' ', e.$fname, NULLIF(e.$mname, ''), e.$lname) as empname"),
            'e.'.Employee::f_Role   . ' as role',
        ];

        $selectFields = array_merge($attendanceFields, $employeeFields);

        $dataset = DB::table(Attendance::getTableName() . ' as a')
        ->whereBetween('a.updated_at', 
        [
            $currentDate->startOfDay()->format(Constants::TimestampFormat), 
            $currentDate->endOfDay()->format(Constants::TimestampFormat)
        ])
        ->where('a.' . Attendance::f_Status, '!=', Attendance::STATUS_ABSENT)
        ->select($selectFields)
        ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('a.updated_at', 'desc')
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

        $pin = decrypt($employee->pin);

        if ($pin != $inputs['input-pin-no'])
            return $errMessage;
        
        return $this->handleAttendance($employee);
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
        //if (!Employee::where('id', $decode[0])->exists())
        $employee = Employee::find($decode[0]);

        if (!$employee)
            return Extensions::encodeFailMessage(Messages::QR_CODE_NOT_RECOGNIZED);

        if ($employee->getAttribute(Employee::f_Role) == Employee::RoleGuard)
            return $this->handleGuardAttendance($employee);
        // else
        //     return Extensions::encodeSuccessMessage("This is an employee");

        return $this->handleAttendance($employee);
    }

    public function handleGuardAttendance(Employee $employee)
    {
        $empId = $employee->id;

        // Check if there's an existing attendance record for the guard today
        $attendance = Attendance::where(Attendance::f_Emp_FK_ID, $empId)
        ->latest()
        //->whereDate('created_at', Carbon::today())
        ->first();
        
        $now = Carbon::now();

        // If there's no existing model record, insert a new one
        // or if there is an existing model, but was already timed out,
        // and is not the same date as today, create a new attendance
        if (!$attendance || 
            (
                $attendance && 
                $attendance->time_out && 
                $now->gt(Carbon::parse($attendance->created_at))
            )
        )
        {
            return $this->insertNewAttendance($empId, true);
        }
        else 
        {
            if ($attendance->time_out)
                return;

            // Check if the created_at date is within 2 days
            $created_at = Carbon::parse($attendance->created_at);
            
            //$now = Carbon::now();
            //$diffInDays = $created_at->diffInDays($now);

            //if ($diffInDays <= 2) 
            //{
                // Update the attendance "clockout" to current date
                // $attendance->setAttribute(Attendance::f_TimeOut, $now);
                // $attendance->save();

                
            //}
            $timeOut = Carbon::now();

            // Then calculate the duration, undertime and so on
            $workHours  = Carbon::parse($attendance->time_in)->diffInSeconds($timeOut) / 3600;
            $duration   = Extensions::durationToTimeString($workHours);

            $attendance->update([
                Attendance::f_TimeOut    => $timeOut,
                Attendance::f_Status     => Attendance::STATUS_PRESENT, //$status,
                Attendance::f_Duration   => $duration,
                Attendance::f_UnderTime  => Constants::ZERO_DURATION,
                Attendance::f_OverTime   => Constants::ZERO_DURATION,
                Attendance::f_LunchStart => Constants::ZERO_DURATION,
                Attendance::f_LunchEnd   => Constants::ZERO_DURATION
            ]);

            return Extensions::encodeSuccessMessage('Clocked out');
        }
    }

    public function handleAttendance(Employee $employee)
    {
        // Handle Attendance Wont Work when the 
        // employee has been marked as Absent

        // Tasks to be added:
        // A1 -> Prevent attendances 1hr before dismissal or after dismissal
        // A2 -> Exclude Sundays and Holidays (Dont allow scans during those days)

        // The current date
        $currentDate = Carbon::now();
    
        // Start a transaction to ensure data consistency
        $data = DB::transaction(function () use ($employee, $currentDate) 
        {
            $empId = $employee->id;

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

                $ignoreLate = $employee->getAttribute(Employee::f_Rank) == StaffConstants::SECURITY_GUARD;

                return $this->insertNewAttendance($empId, $ignoreLate);
            } 
            // If there's an existing record, update it
            else 
            {
                // Guards only need time in and time out
                // if ($employee->getAttribute(Employee::f_Rank) == StaffConstants::SECURITY_GUARD) 
                // {
                //     if ($attendance->time_out)
                //         return;

                //     $timeOut = Carbon::now();

                //     // Then calculate the duration, undertime and so on
                //     $workHours  = Carbon::parse($attendance->time_in)->diffInSeconds($timeOut) / 3600;
                //     $duration   = Extensions::durationToTimeString($workHours);

                //     $attendance->update([
                //         Attendance::f_TimeOut    => $timeOut,
                //         Attendance::f_Status     => Attendance::STATUS_PRESENT, //$status,
                //         Attendance::f_Duration   => $duration,
                //         Attendance::f_UnderTime  => Constants::ZERO_DURATION,
                //         Attendance::f_OverTime   => Constants::ZERO_DURATION,
                //         Attendance::f_LunchStart => Constants::ZERO_DURATION,
                //         Attendance::f_LunchEnd   => Constants::ZERO_DURATION
                //     ]);

                //     return Extensions::encodeSuccessMessage('Clocked out');
                // }

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
    
    private function insertNewAttendance($empId, bool $ignoreLate = false) : string
    {
        $insertAttendance = Attendance::createTimeIn($empId, $ignoreLate);

        if ($insertAttendance)
        {
            $empDetails = DB::table(Employee::getTableName())
                        ->select(Employee::f_FirstName . ' as fname', Employee::f_LastName . ' as lname', Employee::f_Role . ' as role')
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
        $now = Carbon::now();
        $late = $attendance->late ?? Constants::ZERO_DURATION; // Use '00:00:00' if 'late' is null

        // Check if LunchEnd is later than 1pm
        if ($now->hour >= 13) 
        {
            // Calculate the difference in seconds
            $lateSeconds = $now->diffInSeconds(Carbon::createFromTime(13, 0, 0));

            // Convert the 'late' field to seconds
            list($hours, $minutes, $seconds) = explode(':', $late);
            $lateSecondsExisting = $hours * 3600 + $minutes * 60 + $seconds;

            // Add the new late time to the existing one
            $totalLateSeconds = $lateSeconds + $lateSecondsExisting;

            // Convert the total seconds to a time string
            $hours   = floor($totalLateSeconds  / 3600);
            $minutes = floor(($totalLateSeconds / 60) % 60);
            $seconds = $totalLateSeconds % 60;

            // Update the 'late' field
            $late = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $attendance->update([
            Attendance::f_LunchEnd  => $now,
            Attendance::f_Status    => $status,
            Attendance::f_Late      => $late,
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
        $earlyDismissal = Carbon::parse(Attendance::EARLY_DISMISSAL);
        $undertime  = $timeOut->lt($earlyDismissal) ? $earlyDismissal->diffInSeconds($timeOut) / 3600 : 0;
        $overtime   = $workHours > 8 ? $workHours - 8 : 0;

        // Check if TimeOut is before dismissal time 4:50 PM
        // $status = $timeOut->lt(Carbon::parse(Attendance::EARLY_DISMISSAL)) ? 
        //           Attendance::STATUS_UNDERTIME : 
        //           Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_TimeOut   => $timeOut,
            Attendance::f_Status    => Attendance::STATUS_PRESENT, //$status,
            Attendance::f_Duration  => Extensions::durationToTimeString($duration), 
            Attendance::f_UnderTime => Extensions::durationToTimeString($undertime),
            Attendance::f_OverTime  => Extensions::durationToTimeString($overtime), 
        ]);

        return Extensions::encodeSuccessMessage('Clocked out');//, ['status' => $status]);
    }
}