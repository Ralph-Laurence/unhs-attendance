<?php
//Fat Model, Skinny Controller
namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public const HASH_SALT = 'EB7A1F'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    public const RoleTeacher    = 1;
    public const RoleStaff      = 2;

    private const TEACHER = 'Teacher';
    private const STAFF = 'Staff';
    
    public const RoleToString = [
        self::RoleTeacher   => self::TEACHER,
        self::RoleStaff     => self::STAFF
    ];

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
    public const f_PINCode      = 'pin_code';
    public const f_PINFlags     = 'pin_flag';       // -> Enabled | Disabled

    public static function getTableName() : string {
        return (new self)->getTable();
    }

    protected $guarded = [
        'id'
    ];

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

    public static function getRoles() 
    {
        return [
            self::TEACHER  => self::RoleTeacher,
            self::STAFF    => self::RoleStaff
        ];
    }

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

    public function getBasicDetails($id) : array
    {
        $dataset = Employee::where('id', '=', $id)
            ->select([
                Employee::f_EmpNo       . ' as idNo',
                Employee::f_FirstName   . ' as fname',
                Employee::f_MiddleName  . ' as mname',
                Employee::f_LastName    . ' as lname',
                Employee::f_Contact     . ' as phone',
                Employee::f_Email       . ' as email',
                Employee::f_Status      . ' as status',
            ])
            ->first()
            ->toArray();

        return $dataset;
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
            $status . ' as emp_status',
        ]);

        $employeeFields[] = DB::raw("CONCAT_WS(' ', e.$fname, NULLIF(e.$mname, ''), e.$lname) as empname");

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

        return $results;
    }

    public function dissolve($employeeId)
    {
        $messages =
        [
            'fail' =>  [
                self::RoleTeacher   => Messages::TEACHER_DELETE_FAIL,
                self::RoleStaff     => Messages::STAFF_DELETE_FAIL,
            ],
            'success' =>  [
                self::RoleTeacher   => Messages::TEACHER_DELETE_OK,
                self::RoleStaff     => Messages::STAFF_DELETE_OK,
            ],
        ];

        try 
        {
            // Find the employee
            $employee = Employee::where('id', '=', $employeeId)
                ->select([
                    Employee::f_EmpNo    . ' as empNo',
                    Employee::f_Position . ' as role'
                ])
                ->first();

            // Check if employee exists
            if ($employee === null)
                throw new Exception('Employee not found');

            $employee = $employee->toArray();

            // Grab a copy of his employee number. We will use his
            // employee number to identify the qr code filename
            $empNo = $employee['empNo'];
            $role  = $employee['role'];

            $delete = DB::transaction(function () use ($empNo, $employeeId, $role, $messages) 
            {
                // Delete the employee from database
                $rowsDeleted = Employee::where('id', '=', $employeeId)->delete();

                if ($rowsDeleted > 0) 
                {
                    // Delete his qr code file
                    $qrCodeFile = Extensions::getQRCode_storagePath("qr_$empNo.png");

                    if (File::exists($qrCodeFile)) 
                    {
                        if (!File::delete($qrCodeFile))
                            throw new Exception('File deletion failed');
                    }

                    return Extensions::encodeSuccessMessage($messages['success'][$role]);
                } 
                else 
                {
                    return Extensions::encodeFailMessage($messages['fail'][$role]);
                }
            });

            return $delete;
        } 
        catch (Exception $ex) {
            return Extensions::encodeFailMessage($messages['fail'][$role]);
        }
    }
}
