<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('scanner.index')
            ->with('layoutTitles', $layoutTitles)
            ->with('recordsManagementRoute', route(RouteNames::Attendance['index']))
            ->with('scannerPostURL', route(RouteNames::Scanner['decode']));
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

    /**
     * The QR codes contain a HASHED data which are the database ids. 
     * We need to decode those data and process it for attendance
     */
    public function decode(Request $request)
    {
        $hash    = $request->input('hash');
        $hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
        $decode  = $hashids->decode($hash);

        if (is_null($decode) || empty($decode)) {
            return Extensions::encodeFailMessage( Messages::QR_CODE_UNREADABLE );
        }

        return $this->handleAttendance($decode[0]);
    }

    public function handleAttendance(int $empId)
    {
        error_log('employee ' . $empId);
        // Tasks to be added:
        // A1 -> Prevent attendances 1hr before dismissal or after dismissal
        // A2 -> Exclude Sundays and Holidays (Dont allow scans during those days)

        // Check first if the employee id exists
        if (!Employee::where('id', $empId)->exists())
            return Extensions::encodeFailMessage(Messages::QR_CODE_NOT_RECOGNIZED);

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
        $status = $timeOut->lt(Carbon::parse(Attendance::$earlyDismissal)) ? 
                  Attendance::STATUS_UNDERTIME : 
                  Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_TimeOut   => $timeOut,
            Attendance::f_Status    => $status,
            Attendance::f_Duration  => Attendance::formatTimeDuration($duration),
            Attendance::f_UnderTime => Attendance::formatTimeDuration($undertime),
            Attendance::f_OverTime  => Attendance::formatTimeDuration($overtime),
        ]);

        return Extensions::encodeSuccessMessage('Good Bye!', ['status' => $status]);
    }
}