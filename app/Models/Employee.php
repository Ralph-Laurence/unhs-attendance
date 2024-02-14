<?php
//Fat Model, Skinny Controller
namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    public static function getConcatNameDbRaw(string $prefix, $col_as = 'empname', $nameStyle = Constants::NAME_STYLE_WESTERN)
    {
        // If the prefix has no trailing dot, add one
        if ($prefix && !Str::endsWith($prefix, '.'))
            $prefix .= '.';
    
        $fname  = $prefix . self::f_FirstName;
        $mname  = $prefix . self::f_MiddleName;
        $lname  = $prefix . self::f_LastName;
    
        $styles = [
            Constants::NAME_STYLE_WESTERN => "CONCAT_WS(' ', $fname, NULLIF($mname, ''), $lname)",
            Constants::NAME_STYLE_EASTERN => "CONCAT_WS(' ', CONCAT($lname, ','), $fname, NULLIF($mname, ''))"
        ];
    
        if (!in_array($nameStyle, array_keys($styles)))
        {
            error_log('Unknown name style given. Allowed values are eastern and western');
            return '';
        }
    
        $sql = $styles[$nameStyle];
    
        if ($col_as)
            $sql .= " as $col_as";
    
        return DB::raw( $sql );
    }
}
