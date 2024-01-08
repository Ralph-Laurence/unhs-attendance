<?php

namespace App\Http\Controllers;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\RouteNames;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TeachersController extends Controller
{
    private Employee $employeeModel;
    private $hashids;

    public function __construct() 
    {
        $this->employeeModel = new Employee();

        $this->hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    }

    public function index()
    {
        $routes = [
            'defaultDataSource'  => route(RouteNames::Teachers['all']),
            'POST_CreateTeacher' => route(RouteNames::Teachers['create']),
            'DELETE_Teacher'     => route(RouteNames::Teachers['destroy'])
        ];

        $role = Employee::RoleToString[Employee::RoleTeacher];

        return view('backoffice.teachers.index')
            ->with('requireEmail', true)           // Require email in registration
            ->with('descriptiveRole', $role)
            ->with('routes', $routes)
            ->with('empType', encrypt($role));
    }

    public function store(Request $request)
    {
        $input = $this->validateFields($request);

        if ($input['validation_stat'] == 400)
            return json_encode($input);

        $data = [
            Employee::f_EmpNo       => $input['input-id-no'],
            Employee::f_FirstName   => $input['input-fname'],
            Employee::f_MiddleName  => $input['input-mname'],
            Employee::f_LastName    => $input['input-lname'],
            Employee::f_Email       => $input['input-email'],
            Employee::f_Contact     => $input['input-contact'],
            Employee::f_Position    => Employee::RoleTeacher,
            Employee::f_Status      => Employee::ON_STATUS_DUTY
        ];

        try 
        {
            // Save the newly created teacher into database
            $insert = DB::transaction(function () use ($data) 
            {
                return Employee::create($data);
            });

            // Convert the collection to array so that we can use
            // these into the frontend such as adding a new row to
            // the datatable
            $employeeData = $insert->toArray();
            $rowData = [
                'emp_num'       => $employeeData[Employee::f_EmpNo],
                'fname'         => $employeeData[Employee::f_FirstName],
                'mname'         => $employeeData[Employee::f_MiddleName],
                'lname'         => $employeeData[Employee::f_LastName],
                'emp_status'    => $employeeData[Employee::f_Status],
                'total_lates'   => 0,
                'total_leave'   => 0,
                'total_absents' => 0,
                'id'            => $this->hashids->encode($employeeData['id'])
            ];

            // Send the QR code into their email
            $email = $data[Employee::f_Email];

            // The content of QR code is the hashed record id.
            // Also, we will return the path to the generated
            // image file so that we can use it as download link
            // $qrCodePathAsset = null;
            // $qrcode = QRMaker::generateTempFile($rowData['id'], $qrCodePathAsset);

            // // When the checkbox "save qr code local copy" is checked,
            // // we will send a download link to the qr code. Otherwise
            // // send the code via email if it exists. If email was not 
            // // provided, we must send a download link anyway

            // // convert the checkbox value to boolean
            // $option_saveQR_localCopy = filter_var($request->input('save_qr_copy'), FILTER_VALIDATE_BOOLEAN);

            // if ($option_saveQR_localCopy || empty($email))
            // {
            //     $rowData['qrcode_download'] = [
            //         'fileName' => $rowData['emp_num'] . '.png',
            //         'url'      => $qrCodePathAsset
            //     ];
            // }
            // else
            // {
            //     // Replace the #recipient# with firstname
            //     $mailMessage = str_replace('#recipient#', $employeeData[Employee::f_FirstName], Messages::EMAIL_REGISTER_EMPLOYEE);

            //     // Build the email then send it
            //     Mail::raw($mailMessage, function ($message) use ($qrcode, $email) {

            //         // Attach the QR code image into the mail. 
            //         $message->to($email)->subject(Messages::EMAIL_SUBJECT_QRCODE);
            //         $message->embed($qrcode, "qrcode.png");
            //     });

            //     // The Mail::failures() method returns an array of addresses 
            //     // that failed during the last operation performed. If the 
            //     // array is empty, it means that the email was sent successfully 
            //     // to all recipients
            //     if (!Mail::failures()) {
                   
            //         // Delete the file after sending
            //         if (File::exists($qrcode)) {
            //             File::delete($qrcode);
            //         }
            //     }
            // }

            $qrCodePathAsset = null;
            $downloadUrl = null;

            $qrcode = QRMaker::generateTempFile($rowData['id'], $qrCodePathAsset, $downloadUrl);

            // When the checkbox "save qr code local copy" is checked,
            // we will send a download link to the qr code. Otherwise
            // send the code via email if it exists. If email was not 
            // provided, we must send a download link anyway

            if (!empty($email))
            {
                // Replace the #recipient# with firstname
                $mailMessage = str_replace('#recipient#', $employeeData[Employee::f_FirstName], Messages::EMAIL_REGISTER_EMPLOYEE);

                // Build the email then send it
                Mail::raw($mailMessage, function ($message) use ($qrcode, $email) {

                    // Attach the QR code image into the mail. 
                    $message->to($email)->subject(Messages::EMAIL_SUBJECT_QRCODE);
                    $message->embed($qrcode, "qrcode.png");
                });
            }

            // convert the checkbox value to boolean
            $option_saveQR_localCopy = filter_var($request->input('save_qr_copy'), FILTER_VALIDATE_BOOLEAN);

            if ($option_saveQR_localCopy || empty($email))
            {
                // $rowData['qrcode_download'] = [
                //     'fileName' => $rowData['emp_num'] . '.png',
                //     'url'      => $qrCodePathAsset
                // ];
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
            // if (Str::contains($ex->getMessage(), "for key 'employees_emp_no_unique'") )
            // {
            //     return ['validation_stat' => Constants::ValidationStat_Failed] + 
            //            ['errors' => $validator->errors()];
            // }
            return Extensions::encodeFailMessage("Failed " . $ex->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        $recordId = $request->input('rowKey');
        $failMessage = Extensions::encodeFailMessage(Messages::TEACHER_DELETE_FAIL);

        try
        {
            $id = $this->hashids->decode($recordId);

            $rowsDeleted = Employee::where('id', '=', $id)->delete();

            if ($rowsDeleted > 0)
                return Extensions::encodeSuccessMessage(Messages::TEACHER_DELETE_OK);
            else
                return $failMessage;
        }
        catch (Exception $ex) 
        {
            return $failMessage;
        }
    }

    public function getTeachers() 
    {
        $dataset = $this->employeeModel->getTeachers();

        return $dataset;
    }

    public function validateFields(Request $request)
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
        ];

        $employeesUnique = '|unique:' . Employee::getTableName();

        $validationFields = array(
            'input-id-no'   => 'required|regex:'        . RegexPatterns::NUMERIC_DASH,// . $employeesUnique,
            'input-fname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-mname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-lname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-email'   => 'required|max:64',
            'input-contact' => 'nullable|regex:'        . RegexPatterns::MOBILE_NO,
        );

        // Common validation
        $validator = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator->fails())
            return ['validation_stat' => Constants::ValidationStat_Failed] + 
                   ['errors' => $validator->errors()];
        
        $inputData = ['validation_stat' => Constants::ValidationStat_Success] + 
                     ['errors' => $validator->validated()] + $validator->validated();

        return $inputData;
    }
}

// {"validation_stat":400,"errors":{"input-id-no":["ID Number may only contain numbers and dashes."]}}
