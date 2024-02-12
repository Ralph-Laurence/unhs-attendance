<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    }

    public function store(Request $request)
    {
        $inputs = $this->validateFields($request);

        if ($inputs['validation_stat'] == Constants::ValidationStat_Failed)
            return json_encode($inputs);

        try 
        {
            $insertResults = $this->addEmployee($inputs)->toArray();

            // Convert the collection to array so that we can use
            // these into the frontend such as adding a new row to
            // the datatable
            $encodedId    = $this->hashids->encode($insertResults['id']);
            $empNum       = $insertResults[Employee::f_EmpNo];

            $rowData = [
                'emp_num'       => $empNum,
                'fname'         => $insertResults[Employee::f_FirstName],
                'mname'         => $insertResults[Employee::f_MiddleName],
                'lname'         => $insertResults[Employee::f_LastName],
                'emp_status'    => $insertResults[Employee::f_Status],
                'total_lates'   => 0,
                'total_leave'   => 0,
                'total_absents' => 0,
                'id'            => $encodedId
            ];

            // To send the QR code into their email, we
            // must first read the email from the newly
            // inserted data
            $email = $insertResults[Employee::f_Email];

            // The content of QR code is the hashed record id.
            // Also, we will return the path to the generated
            // image file so that we can use it as download link
            $qrCodePathAsset = null;
            $downloadUrl = null;

            $qrcode_filename = "qr_$empNum.png";

            $qrcode = QRMaker::saveFile($encodedId, $qrcode_filename, $qrCodePathAsset, $downloadUrl);

            // If the email was provided, send the qr code via email
            if (!empty($email))
            {
                $str_search  = ['#recipient#', '#pin#'];
                $str_replace = [ $insertResults[Employee::f_FirstName], $insertResults[Employee::f_PINCode] ];

                // Replace the #recipient# with firstname
                $mailMessage = str_replace($str_search, $str_replace, Messages::EMAIL_REGISTER_EMPLOYEE);
                
                // Build the email then send it
                Mail::raw($mailMessage, function ($message) use ($qrcode, $email) {

                    // Attach the QR code image into the mail. 
                    $message->to($email)->subject(Messages::EMAIL_SUBJECT_QRCODE);
                    $message->embed($qrcode, "qrcode.png");
                });
            }

            // convert the checkbox value to boolean
            $option_saveQR_localCopy = filter_var($request->input('save_qr_copy'), FILTER_VALIDATE_BOOLEAN);

            // When the checkbox "save qr code local copy" is checked,
            // we will send a download link to the qr code. Otherwise
            // send the code via email if it exists. If email was not 
            // provided, we must send a download link anyway
            if ($option_saveQR_localCopy || empty($email))
            {
                $rowData['qrcode_download'] = [
                    'fileName' => $rowData['emp_num'] . '.png',
                    'url'      => $downloadUrl
                ];
            }
            
            // Return AJAX response
            return Extensions::encodeSuccessMessage("Success!", $rowData);
        } 
        catch (\Exception $ex) 
        {
            $emailError = "failed with errno=10054 An existing connection was forcibly closed by the remote host";
          
            if (Str::contains($ex->getMessage(), $emailError) )
            {
                return Extensions::encodeFailMessage("Record successfully saved but failed to send QR Code through email.");
            }

            return Extensions::encodeFailMessage("Request Failed\n\n" . $ex->getMessage());
        }
    }

    private function addEmployee($inputs)
    {
        $inputs['input-role'] = array_flip(Employee::RoleToString)[
            $inputs['input-role']
        ];

        $data = [
            Employee::f_EmpNo       => $inputs['input-id-no'],
            Employee::f_FirstName   => $inputs['input-fname'],
            Employee::f_MiddleName  => $inputs['input-mname'],
            Employee::f_LastName    => $inputs['input-lname'],
            Employee::f_Email       => $inputs['input-email'],
            Employee::f_Contact     => $inputs['input-contact'],
            Employee::f_Position    => $inputs['input-role'],
            Employee::f_Status      => Employee::ON_STATUS_DUTY,
            Employee::f_PINCode     => encrypt(random_int(1000, 9999))       // 4-digit PIN
        ];

        // Save the newly created employee into database
        $insert = DB::transaction(function () use ($data) 
        {
            return Employee::create($data);
        });

        return $insert;
    }

    public function destroy(Request $request)
    {
        $key = $request->input('rowKey');
        $id = $this->hashids->decode($key);

        $employee = new Employee;
        return $employee->dissolve($id);
    }

    // Performs a database update
    public function update(Request $request)
    {
        $key = $request->input('row-key');

        if (empty($key))
        {
            $code = Constants::RecordId_Empty;
            $msg = Messages::UPDATE_FAIL_CANT_IDENTIFY_RECORD;

            return Extensions::encodeFailMessage("$msg\n\n(Error Code $code)", $code);
        }

        $id = $this->hashids->decode($key);
        $input = $this->validateFields($request, $id[0]);

        if ($input['validation_stat'] == Constants::ValidationStat_Failed)
            return json_encode($input);

        $data = [
            Employee::f_EmpNo       => $input['input-id-no'],
            Employee::f_FirstName   => $input['input-fname'],
            Employee::f_MiddleName  => $input['input-mname'],
            Employee::f_LastName    => $input['input-lname'],
            Employee::f_Email       => $input['input-email'],
            Employee::f_Contact     => $input['input-contact'],
            //Employee::f_Status      => Employee::ON_STATUS_DUTY
        ];

        try 
        {
            // Save the updated employee data into database
            $update = DB::transaction(function () use ($data, $id) 
            {
                $employee = Employee::where('id', '=', $id[0])->first();

                if ($employee)
                {
                    $employee->update($data);
                    return $employee;
                }
                else
                    return -1;
            });

            if (is_int($update) && $update == -1)
            {
                $code = Constants::RecordNotFound;
                $msg = Messages::UPDATE_FAIL_NON_EXISTENT_RECORD;

                return Extensions::encodeFailMessage("$msg.\n\n(Error Code $code)", $code);
            }

            // Convert the collection to array so that we can use
            // these into the frontend such as adding a new row to
            // the datatable
            $employeeData = $update->toArray();
            $rowData = [
                'emp_num'       => $employeeData[Employee::f_EmpNo],
                'fname'         => $employeeData[Employee::f_FirstName],
                'mname'         => $employeeData[Employee::f_MiddleName],
                'lname'         => $employeeData[Employee::f_LastName],
                'emp_status'    => $employeeData[Employee::f_Status]
            ];
            
            // Return AJAX response
            return Extensions::encodeSuccessMessage("Success!", $rowData);
        } 
        catch (\Exception $ex) 
        {    
            //return Extensions::encodeFailMessage("Failed " . $ex->getMessage());
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED, Constants::InternalServerError);
        }
    }

    // Load the employee details
    public function details(Request $request)
    {
        try
        {
            $key = $request->input('key');
            $id = $this->hashids->decode($key);

            $employee = new Employee;
            $dataset = $employee->getBasicDetails($id[0]);

            return Extensions::encodeSuccessMessage('Basic information loaded for edit mode', $dataset);
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    public function validateFields(Request $request, $ignoreId = null)
    {
        $validationMessages = 
        [
            'input-id-no.required' => ValidationMessages::required('ID Number'),
            'input-id-no.regex'    => ValidationMessages::numericDash('ID Number'),
            'input-id-no.unique'   => ValidationMessages::unique('ID Number'),

            'input-fname.required' => ValidationMessages::required('Firstname'),
            'input-fname.max'      => ValidationMessages::maxLength(32, 'Firstname'),
            'input-fname.regex'    => ValidationMessages::alphaDashDotSpace('Firstname'),

            'input-mname.required' => ValidationMessages::required('Middlename'),
            'input-mname.max'      => ValidationMessages::maxLength(32, 'Middlename'),
            'input-mname.regex'    => ValidationMessages::alphaDashDotSpace('Middlename'),

            'input-lname.required' => ValidationMessages::required('Lastname'),
            'input-lname.max'      => ValidationMessages::maxLength(32, 'Lastname'),
            'input-lname.regex'    => ValidationMessages::alphaDashDotSpace('Lastname'),

            'input-email.required' => ValidationMessages::required('Email'),
            'input-email.unique'   => ValidationMessages::unique('Email'),
        ];

        $employeeTable = Employee::getTableName();
        
        $validationFields = array(
            'input-id-no'   => [
                'required',
                'regex:' . RegexPatterns::NUMERIC_DASH,
                Rule::unique($employeeTable, Employee::f_EmpNo)
            ],
            'input-fname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-mname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-lname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-email'   => [
                'required',
                'email',
                'max:64',
                Rule::unique('users', Employee::f_Email),
                Rule::unique($employeeTable, Employee::f_Email),
            ],
            'input-contact' => 'nullable|regex:'        . RegexPatterns::MOBILE_NO,
            'input-role'    => 'required|not_in:'       . implode(',', array_keys(Employee::RoleToString))
        );

        //
        // This will be used to ignore the validation rule during Update
        //
        if ($ignoreId && !is_null($ignoreId))
        {
            $validationFields['input-email'] = [
                'required',
                'email',
                'max:64',
                Rule::unique('users', 'email')->ignore($ignoreId),
                Rule::unique(Employee::getTableName(), Employee::f_Email)->ignore($ignoreId),
            ];

            $validationFields['input-id-no'] = [
                'required',
                'regex:' . RegexPatterns::NUMERIC_DASH,
                Rule::unique($employeeTable, Employee::f_EmpNo)->ignore($ignoreId)
            ];
        }

        // Common validation
        $validator = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator->fails())
            return ['validation_stat' => Constants::ValidationStat_Failed] + 
                   ['errors' => $validator->errors()];
        
        $inputData = ['validation_stat' => Constants::ValidationStat_Success] + 
                     ['errors' => $validator->validated()] + $validator->validated();

        return $inputData;
    }

    public function loadAutoSuggest_EmpNo()
    {
        $f_empNo  = Employee::f_EmpNo;
        $fname    = Employee::f_FirstName;
        $mname    = Employee::f_MiddleName;
        $lname    = Employee::f_LastName;

        // These data will be applied to the options of the auto-suggest input.
        // For consistency, the displayed text will be called as 'label' while
        // the actual data will be called 'value'.
        //
        $empIds = Employee::orderBy($f_empNo)->select([
            $f_empNo . ' as value',

            // Concatenate the names then do not include spaces if mname is empty
            DB::raw("CONCAT_WS(' ', $lname, ',', $fname, NULLIF($mname, '')) as label")
        ])
        ->get()
        ->toArray();

        return json_encode($empIds);
    }
}
