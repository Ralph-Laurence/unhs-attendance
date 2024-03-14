<?php
//Fat Model, Skinny Controller
namespace App\Models;

use App\Http\Controllers\backoffice\StaffController;
use App\Http\Controllers\backoffice\TeachersController;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\EmployeeQRMail;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Http\Utils\RouteNames;
use App\Models\Constants\FacultyConstants;
use App\Models\Constants\StaffConstants;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function getMorphClass()
    {
        return get_class($this);
    }

    public const HASH_SALT = 'EB7A1F'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    public const RoleTeacher    = 1;
    public const RoleStaff      = 2;

    public const STR_ROLE_TEACHER = 'Teacher';
    public const STR_ROLE_STAFF   = 'Staff';

    public const STR_COLLECTIVE_ROLE_ALL     = 'Employee';
    public const STR_COLLECTIVE_ROLE_FACULTY = 'Faculty';
    
    public const RoleToString = [
        self::RoleTeacher   => self::STR_ROLE_TEACHER,
        self::RoleStaff     => self::STR_ROLE_STAFF
    ];

    public const ON_STATUS_LEAVE = 'Leave';
    public const ON_STATUS_DUTY  = 'On Duty';

    public const f_EmpNo        = 'emp_no';         // -> Employee Number
    public const f_FirstName    = 'firstname';
    public const f_MiddleName   = 'middlename';
    public const f_LastName     = 'lastname';
    public const f_Role         = 'role';
    public const f_Rank         = 'rank';
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

    // Need to place this in here, for object relational mapping
    protected $table = Constants::TABLE_EMPLOYEES;

    // For other uses
    public static function getTableName() : string {
        return Constants::TABLE_EMPLOYEES;
    }

    // This is the friendly name that will be used when 
    // presenting this model in the Audits table.
    public static function getFriendlyName() : string {
        return 'Employees';
    }

    protected $guarded = [
        'id'
    ];

    public static function getRoles() 
    {
        return [
            self::STR_ROLE_TEACHER  => self::RoleTeacher,
            self::STR_ROLE_STAFF    => self::RoleStaff
        ];
    }

    public function getBasicDetails($id, $hashedKey = '') : string
    {
        try
        { 
            $dataset = Employee::where('id', '=', $id)
                ->select([
                    self::f_EmpNo   . ' as idNo',
                    self::f_Contact . ' as phone',
                    self::f_Email   . ' as email',
                    self::f_Status  . ' as status',
                    self::f_Role    . ' as role',
                    self::f_Rank    . ' as rank'
                ])
                ->selectRaw( self::getConcatNameDbRaw('', 'empname', Constants::NAME_STYLE_EASTERN) )
                ->firstOrFail()
                ->toArray();

            switch ($dataset['role'])
            {
                case Employee::RoleTeacher:
                    $dataset['rank'] = FacultyConstants::describeRank( $dataset['rank'] );
                    break;

                case Employee::RoleStaff:
                    $dataset['rank'] = StaffConstants::describeRank( $dataset['rank'] );
                    break;
            }

            // This QR image is generated as Base64 URI with its content from Hashed Key. 
            // When the hashed key is not provided, we simply return 404 string.
            $dataset['qrCode']       = '404';
            $dataset['qrCodeResend'] = route(RouteNames::Employee['resendqr']);

            if ( !empty($hashedKey) )
            {
                $qrcode = QRMaker::generate($hashedKey, true);

                $dataset['qrCode'] = $qrcode['imageUrl'];
                $dataset['qrBlob'] = $qrcode['blobUrl'];
                $dataset['qrFile'] = 'qr-code-'.$dataset['idNo'].'-'.$dataset['empname'].'.png';
            }

            return json_encode([
                'code'      => Constants::XHR_STAT_OK,
                'dataset'   => $dataset
            ]);

        }
        catch (ModelNotFoundException $ex) {
            // When no records of leave or employee were found
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        }
        catch (\Exception $ex) {
            error_log($ex->getMessage());
            // Common errors
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    public function getEmployees(int $role)
    {
        $dataset = $this->buildSelectQuery($role)->get();

        Extensions::hashRowIds($dataset, self::HASH_SALT);

        return json_encode([
            'data' => $dataset->toArray(),
        ]);
    }

    private function buildSelectQuery(int $role)
    {
        $a_late   = Attendance::f_Late;
        $a_status = Attendance::f_Status;
        $v_absent = Attendance::STATUS_ABSENT;
        $l_empfk  = LeaveRequest::f_Emp_FK_ID;
        $e_status = 'e.' . self::f_Status;
        $e_idNo   = 'e.' . self::f_EmpNo;

        $e_status_active    = self::ON_STATUS_DUTY;
        $e_status_inactive  = self::ON_STATUS_LEAVE;

        $group    = array_merge(Extensions::prefixArray('e.', [
            self::f_EmpNo,
            self::f_FirstName,
            self::f_MiddleName,
            self::f_LastName,
            self::f_Status,
            'id'
        ]), [
            'l.total_leave'
        ]);

        $query = DB::table(self::getTableName() . ' AS e')
            ->where('e.' . self::f_Role, '=', $role)
            ->select(
                self::getConcatNameDbRaw('e', 'empname', Constants::NAME_STYLE_EASTERN)
            )
            ->selectRaw(
                "e.id,
                $e_status AS emp_status,
                $e_idNo AS emp_num,
                COUNT(a.$a_late) AS total_lates,
                SUM(CASE WHEN a.$a_status = '$v_absent' THEN 1 ELSE 0 END) AS total_absents,
                CASE 
                    WHEN $e_status = '$e_status_active' THEN 'active'
                    WHEN $e_status = '$e_status_inactive' THEN 'inactive'
                END as status_style,
                l.total_leave"
            )
            ->leftJoin(Attendance::getTableName() . ' AS a', 'a.' . Attendance::f_Emp_FK_ID, 'e.id')
            ->leftJoinSub(
                // Query
                DB::table(LeaveRequest::getTableName())
                    ->select($l_empfk)
                    ->selectRaw("COUNT(id) AS total_leave")
                    ->where(LeaveRequest::f_LeaveStatus, LeaveRequest::LEAVE_STATUS_APPROVED)
                    ->groupBy($l_empfk),

                // Join alias
                'l',

                // On
                function ($join) use ($l_empfk) {
                    $join->on('l.' . $l_empfk, '=', 'e.id');
                }
            )
            ->groupBy( $group )
            ->orderBy($e_idNo);
        
        return $query;
    }

    public function dissolve(int $employeeId)
    {
        try 
        {
            // Make sure that employee exists before deleting
            $employee = Employee::findOrFail($employeeId);

            $delete = $employee->delete();

            if (!$delete)
                throw new Exception;

            return Extensions::encodeSuccessMessage(Messages::GENERIC_DELETE_OK);
        } 
        catch (ModelNotFoundException $ex){
            return Extensions::encodeFailMessage(Messages::MODIFY_FAIL_INEXISTENT);    
        }
        catch (Exception $ex) {
            return Extensions::encodeFailMessage(Messages::GENERIC_DELETE_FAIL);
        }
    }

    public function insert(array $data)
    {
        try
        {
            $model = Employee::create( $data['insertData'] );

            // This hashed row id can be used both as a record identifier
            // on the frontend tables, and as a QR Code content
            $hashids      = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
            $hashedRowId  = $hashids->encode($model->getAttribute('id'));
            
            $extraData = $data['extraData'];

            $emp_firstname = $model->getAttribute( self::f_FirstName );
            $emp_lastname  = $model->getAttribute( self::f_LastName  );
            $emp_number    = $model->getAttribute( self::f_EmpNo );

            $frontendData = [
                'emp_num'       => $emp_number,
                'emp_status'    => $model->getAttribute(self::f_Status),
                'id'            => $hashedRowId,
                'total_lates'   => 0,
                'total_leave'   => 0,
                'total_absents' => 0,
                'empname'       => implode(' ', [
                    $emp_lastname . ',',
                    $emp_firstname,
                    $model->getAttribute( self::f_MiddleName ),
                ])
            ];

            $qrcode = QRMaker::createAndSend($model, [
                'qrContent'  => $hashedRowId,
                'rawPinCode' => $extraData['rawPinCode'],
                'saveQRCode' => $extraData['saveQRCode'],
                'viewName'   => 'emails.newemployee'
            ]);

            if (is_array($qrcode) && array_key_exists('blob', $qrcode))
            {
                $frontendData['download'] = $qrcode['blob'];
                $frontendData['filename'] = $qrcode['file'];
            }

            return Extensions::encodeSuccessMessage("Success!", $frontendData);
        }
        catch (QueryException $e) 
        {
            // Handle the error
            echo $e->getMessage();            
            return Extensions::encodeFailMessage('[Database Error] : '. Messages::CANT_SAVE_RECORD);   
        }
        catch (\Exception $ex) 
        {
            error_log($ex->getMessage());
            $smtpErr = "failed with errno=10054 An existing connection was forcibly closed by the remote host";
          
            if (Str::contains($ex->getMessage(), $smtpErr))
                return Extensions::encodeFailMessage("Record successfully saved but failed to send QR Code through email.");

            return Extensions::encodeFailMessage(Messages::GENERIC_INSERT_FAIL);
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

    // Object Relational Mapping; Each employee can have many leave requests
    public function leave_requests() 
    {
        return $this->hasMany(\App\Models\LeaveRequest::class, LeaveRequest::f_Emp_FK_ID);
    }
    
}
