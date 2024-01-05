<?php

namespace App\Models;

use App\Http\Utils\Extensions;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;

    public const HASH_SALT = 'EB7A1F'; // Just random string, nothing special

    public const RoleToString = [
        self::RoleTeacher   => 'Teacher',
        self::RoleStaff     => 'Staff'
    ];

    public const RoleTeacher    = 0;
    public const RoleStaff      = 1;

    public const ON_STATUS_LEAVE = 'Leave';
    public const ON_STATUS_DUTY  = 'On Duty';

    public const f_EmpNo        = 'emp_no';         // -> Employee Number
    public const f_FirstName    = 'firstname';
    public const f_MiddleName   = 'middlename';
    public const f_LastName     = 'lastname';
    public const f_Position     = 'position';
    public const f_Email        = 'email';
    public const f_Contact      = 'contact';
    public const f_Photo        = 'photo';
    public const f_Status       = 'status';         // -> Status: On Duty | Leave
    public const f_QrSecLevel   = 'qr_sec_level';   // -> Security Levels: None, Medium, High
                                                    // -> None   - No security
                                                    // -> Medium - PIN Code
                                                    // -> High   - App
    public static function getTableName() : string {
        return (new self)->getTable();
    }

    /**
        SELECT 
            e.id, 
            e.firstname, 
            e.middlename, 
            e.lastname, 
            e.status AS employee_status,
            SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS total_lates,
            SUM(CASE WHEN a.late IS NOT NULL THEN 1 ELSE 0 END) AS total_lates,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS total_absents,
            COUNT(l.id) AS total_leaves
        FROM 
            employees e
        LEFT JOIN 
            attendance a ON e.id = a.emp_fk_id
        LEFT JOIN
            leave_requests l ON e.id = l.emp_fk_id
        GROUP BY 
            e.id;
     */
    public function getTeachers()
    {
        $dataset = $this->getEmployees(Employee::RoleTeacher);

        Extensions::hashRowIds($dataset, self::HASH_SALT);

        return json_encode([
            'data' => $dataset->toArray(),
        ]);
    }

    public function getStaff()
    {
        $dataset = $this->getEmployees(Employee::RoleStaff);

        Extensions::hashRowIds($dataset, self::HASH_SALT);

        return json_encode([
            'data' => $dataset->toArray(),
        ]);
    }

    private function getEmployees(int $role)
    {
        $fname  = Employee::f_FirstName;
        $mname  = Employee::f_MiddleName;
        $lname  = Employee::f_LastName;
        $status = Employee::f_Status;
        $empNo  = Employee::f_EmpNo;

        $employeeFields = Extensions::prefixArray('e.', [
            'id',
            $empNo  . ' as emp_num',
            $fname  . ' as fname',
            $mname  . ' as mname',
            $lname  . ' as lname',
            $status . ' as emp_status',
        ]);

        $a_field_status = Attendance::f_Status;
        $absents = Attendance::STATUS_ABSENT;
        $late = Attendance::f_Late;

        $results = DB::table(self::getTableName()   . ' as e')
            ->leftJoin(Attendance::getTableName()   . ' as a', 'e.id', '=', 'a.' . Attendance::f_Emp_FK_ID)
            ->leftJoin(LeaveRequest::getTableName() . ' as l', 'e.id', '=', 'l.' . LeaveRequest::f_Emp_FK_ID)
            ->select($employeeFields)
            ->selectRaw("SUM(CASE WHEN a.$late IS NOT NULL THEN 1 ELSE 0 END) as total_lates")
            ->selectRaw("SUM(CASE WHEN a.$a_field_status = '$absents' THEN 1 ELSE 0 END) as total_absents")
            ->selectRaw('COUNT(l.id) as total_leave')
            ->where('e.' . Employee::f_Position, '=', $role)
            ->groupBy('e.id', "e.$empNo", "e.$fname", "e.$mname", "e.$lname", "e.$status")
            ->get();

        // $hashids = new Hashids; //(self::HASH_SALT, 10);

        // // Hash the employee id
        // $results->map(function ($item) use ($hashids) 
        // {
        //     $item->id = $hashids->encode($item->id);
        //     return $item;
        // });

        return $results;
    }
}
