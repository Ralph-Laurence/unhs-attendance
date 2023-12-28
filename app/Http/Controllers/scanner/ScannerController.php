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
    private const XHR_STAT_OK    = 0;
    private const XHR_STAT_FAIL  = -1;

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
        ->select($selectFields)
        ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('a.created_at', 'desc')
        ->limit(10)
        ->get();

        return json_encode([
            'data'  => $dataset->toArray(),
            'icon' => [
                Attendance::STATUS_PRESENT     => 'present',
                Attendance::STATUS_BREAK       => 'break',
                Attendance::STATUS_UNDERTIME => 'undertime'
            ]
        ]);
    }

    /**
     * The QR codes contain a HASHED data which are the database ids. 
     * We need to decode those data and process it for attendance
     */
    public function decode(Request $request)
    {
        $hash    = $request->input('hash');
        $hashids = new Hashids();
        $decode  = $hashids->decode($hash);

        if (is_null($decode) || empty($decode)) {
            return $this->encodeFailMessage( Messages::QR_CODE_UNREADABLE );
        }

        return $this->handleAttendance($decode[0]);
    }

    public function handleAttendance(int $empId)
    {
        // Tasks to be added:
        // A1 -> Prevent attendances 1hr before dismissal or after dismissal
        // A2 -> Exclude Sundays and Holidays (Dont allow scans during those days)

        // Check first if the employee id exists
        if (!Employee::where('id', $empId)->exists())
            return $this->encodeFailMessage(Messages::QR_CODE_NOT_RECOGNIZED);

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

            return $this->encodeSuccessMessage('Have a nice day!', [
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

        return $this->encodeSuccessMessage('Out for lunch...', ['status'  => $status]);
    }

    private function updateLunchEnd(Attendance $attendance)
    {
        $status = Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_LunchEnd  => Carbon::now(),
            Attendance::f_Status    => $status,
        ]);

        return $this->encodeSuccessMessage('Welcome Back!', ['status'  => $status]);
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

        //$undertime  = $workHours < 8 ? 8 - $workHours : 0;
        $overtime   = $workHours > 8 ? $workHours - 8 : 0;

        $workStart  = Carbon::parse(Attendance::$workStartTime);
        $timeIn     = Carbon::parse($attendance->time_in);
        $late       = $timeIn->gt($workStart) ? $timeIn->diffInSeconds($workStart) / 3600 : 0;

        // Check if TimeOut is before dismissal time 4:50 PM
        $status = $timeOut->lt(Carbon::parse(Attendance::$earlyDismissal)) ? 
                  Attendance::STATUS_UNDERTIME : 
                  Attendance::STATUS_PRESENT;

        $attendance->update([
            Attendance::f_TimeOut   => $timeOut,
            Attendance::f_Status    => $status,
            Attendance::f_Duration  => $this->formatDuration($duration),
            Attendance::f_UnderTime => $this->formatDuration($undertime),
            Attendance::f_OverTime  => $this->formatDuration($overtime),
            Attendance::f_Late      => $this->formatDuration($late),
        ]);

        return $this->encodeSuccessMessage('Good Bye!', ['status' => $status]);
    }

    private function formatDuration($duration)
    {
        $hours   = floor($duration);
        $minutes = floor(($duration - $hours) * 60);
        $seconds = floor((($duration - $hours) * 60 - $minutes) * 60);
    
        $result = '';
        if ($hours > 0)
            $result .= $hours . 'Hr' . ($hours > 1 ? 's' : '') . ' ';
        
        if ($minutes > 0)
            $result .= $minutes . 'min' . ($minutes > 1 ? 's' : '') . ' ';
        
        if ($seconds > 0)
            $result .= $seconds . 'sec' . ($seconds > 1 ? 's' : '');
    
        return trim($result);
    }
    
    private function encodeSuccessMessage($message, $extraRows = []) : string 
    {
        // Use the array union operator (+) to merge the arrays
        $result = ['code' => self::XHR_STAT_OK, 'message' => $message] + $extraRows;
    
        return json_encode($result);
    }

    private function encodeFailMessage($message) : string 
    {
        return json_encode([
            'code'    => self::XHR_STAT_FAIL,
            'message' => $message
        ]);
    }
}

// https://www.simplesoftware.io/#/docs/simple-qrcode
// https://github.com/vinkla/hashids