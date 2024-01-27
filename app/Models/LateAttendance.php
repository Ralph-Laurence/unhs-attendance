<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LateAttendance extends Model
{
    use HasFactory;

    public const f_Emp_FK_ID    = 'emp_fk_id';      // Employee Foreign Key ID
    public const f_Status       = 'status';         // Status -> Present, Break, Absent
    public const f_WeekNo       = 'week_no';
    
     /**
     * Retrieve all attendances that were made today
     */
    public function getDailyLates(Request $request)
    {
        // The current timestamp
        $currentDate = Carbon::now();

        // Instead of whereDate($today), we will use where between
        $dataset = $this->buildAbsenceQuery()
            ->whereBetween('a.created_at', 
            [
                $currentDate->startOfDay()->format(Constants::TimestampFormat), 
                $currentDate->endOfDay()->format(Constants::TimestampFormat)
            ]);

        $this->applyRoleFilter($request, $dataset);
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);
        
        return $this->encodeAttendanceData($request, $dataset, 'Today');
    }

    public function getWeeklyLates(Request $request)
    {
        $currentWeek = Extensions::getCurrentWeek();

        $dataset = $this->buildAbsenceQuery()
                   ->where('a.' . Attendance::f_WeekNo, '=', $currentWeek);

        $this->applyRoleFilter($request, $dataset);
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);

        return $this->encodeAttendanceData($request, $dataset, "This Week (week #$currentWeek)");
    }

    public function getMonthlyLates(Request $request)
    {
        if (!$request->filled('monthIndex'))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $monthIndex = $request->input('monthIndex');

        $dataset = $this->buildAbsenceQuery()
            ->whereMonth('a.created_at', '=', $monthIndex);
        
        $this->applyRoleFilter($request, $dataset);
        $dataset = $dataset->get();

        Extensions::hashRowIds($dataset);

        $monthName = Carbon::createFromFormat('!m', $monthIndex)->monthName;

        return $this->encodeAttendanceData($request, $dataset, "Month of $monthName");
    }

    private function applyRoleFilter(Request &$request, &$dataset)
    {
        if (
            ($request->has('role') && $request->filled('role')) && 
            ($request->input('role') != Constants::RECORD_FILTER_ALL)
        )
        {
            $role  = $request->input('role');
            $roles = Employee::RoleToString; 
            
            if (!in_array($role, $roles))
            {
                $request->replace(['role' => Constants::RECORD_FILTER_ALL]);
                return;
            }

            $dataset->where('e.'.Employee::f_Position, '=', array_flip($roles)[ $role ]);
        }
    }

    /**
    * Base query builder for retrieving attendances 
    */
    private function buildAbsenceQuery()
    {
        $role = 'e.' . Employee::f_Position;
        $roles = Employee::RoleToString;

        $roleMapping = "CASE ";
        
        foreach ($roles as $key => $value) {
            $roleMapping .= "WHEN $role = $key THEN '$value' ";
        }

        $roleMapping .= "END as role";
    
        $fname = Employee::f_FirstName;
        $mname = Employee::f_MiddleName;
        $lname = Employee::f_LastName;

        $employeeFields = [
            DB::raw("CONCAT_WS(' ', e.$fname, NULLIF(e.$mname, ''), e.$lname) as emp_fullname"),
            'e.'. Employee::f_EmpNo . ' as idNo',
        ];

        $attendanceFields = Extensions::prefixArray('a.', [
            'id',
            'created_at' ,
        ]);

        $fields = array_merge([
            DB::raw("'" . Attendance::STATUS_LATE . "' as status"),
            DB::raw($roleMapping)
        ], $attendanceFields, $employeeFields);
        
        $query = DB::table(Attendance::getTableName() . ' as a')
                ->select($fields)
                ->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
                ->whereNotNull('a.' . Attendance::f_Late)
                ->where('a.' . Attendance::f_Late, '<>', '')
                ->orderBy('a.created_at', 'desc');

        error_log($query->toSql());

        return $query;
    }

    private function encodeAttendanceData(Request &$request, $dataset, $descriptiveRange = null)
    {
        $filters = [
            'select_range' => $request->input('range')
        ];

        if ($request->has('monthIndex'))
            $filters['month_index'] = $request->input('monthIndex');

        if ($request->has('role'))
            $filters['select_role'] = $request->input('role');
        else
            $filters['select_role'] = Constants::RECORD_FILTER_ALL;

        return json_encode([
            'data'      => $dataset->toArray(),
            'range'     => $descriptiveRange,
            'filters'   => $filters,
            'icon'      => Attendance::getIconClasses()
        ]);
    }
}