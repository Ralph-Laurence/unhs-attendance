<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePostRequest;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Models\Employee;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    }

    // Load the employee details
    public function show(Request $request)
    {
        $key = $request->input('key');
        $id  = $this->hashids->decode($key);

        $employee = new Employee;
        $dataset  = $employee->getBasicDetails($id[0], $key);

        return $dataset;
    }

    public function store(EmployeePostRequest $request)
    {
        try
        {
            $inputs        = $request->validated();
            $insertResults = $this->addEmployee( $inputs );
            $saveLocalCopy = $inputs['option-save-qr'] == Constants::CHECKBOX_ON;
            $frontendData  = $insertResults['frontEndData'];

            $qrcode = $this->sendEmployeeQR(
                $insertResults['modelInstance'], 
                $saveLocalCopy
            );

            if (!empty($qrcode))
                $frontendData['download'] = $qrcode;

            return Extensions::encodeSuccessMessage("Success!", $frontendData);
        }
        catch (\Exception $ex) 
        {
            error_log($ex->getMessage());
            $smtpErr = "failed with errno=10054 An existing connection was forcibly closed by the remote host";
          
            if (Str::contains($ex->getMessage(), $smtpErr))
                return Extensions::encodeFailMessage("Record successfully saved but failed to send QR Code through email.");

            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        }
    }

    // Performs a database update
    public function update(EmployeePostRequest $request)
    {
        $data = [
            Employee::f_EmpNo       => $request->input('input-id-no'),
            Employee::f_FirstName   => $request->input('input-fname'),
            Employee::f_MiddleName  => $request->input('input-mname'),
            Employee::f_LastName    => $request->input('input-lname'),
            Employee::f_Email       => $request->input('input-email'),
            Employee::f_Contact     => $request->input('input-phone'),
            Employee::f_Rank        => $request->input('input-position')
        ];

        try
        {
            $id = $this->hashids->decode( $request->input('update-key') );

            $model = DB::transaction(function() use($data, $id)
            {
                $employee = Employee::where('id', '=', $id[0])->firstOrFail();
                $employee->update($data);

                return $employee;
            });

            $frontendData = [
                'emp_num'       => $model->getAttribute(Employee::f_EmpNo),
                'id'            => $request->input('update-key'),
                'empname'       => implode(' ', [
                    $model->getAttribute(Employee::f_LastName).',',
                    $model->getAttribute(Employee::f_FirstName),
                    $model->getAttribute(Employee::f_MiddleName),
                ])
            ];

            return Extensions::encodeSuccessMessage(
                Messages::EMPLOYEE_UPDATE_OK,
                $frontendData
            );
        }
        catch (ModelNotFoundException $ex) {
            // When no records of leave or employee were found
            return Extensions::encodeFailMessage(Messages::MODIFY_FAIL_INEXISTENT);
        }
        catch (\Exception $ex) {
            // Common errors
            return Extensions::encodeFailMessage(Messages::REVERT_TRANSACT_ON_FAIL);
        }
    }
   
    public function edit(Request $request)
    {
        $hashids = new Hashids(
            Employee::HASH_SALT, 
            Employee::MIN_HASH_LENGTH
        );

        try
        {
            $key = $request->input('key');
            $id  = $hashids->decode($key);

            if (empty($id))
                throw new ModelNotFoundException;

            $dataset = Employee::where('id', '=', $id[0])
                ->select([
                    Employee::f_FirstName   . ' as fname',
                    Employee::f_MiddleName  . ' as mname',
                    Employee::f_LastName    . ' as lname',
                    Employee::f_Contact     . ' as phone',
                    Employee::f_EmpNo       . ' as idNo',
                    Employee::f_Rank        . ' as rank',
                    Employee::f_Email       . ' as email'
                ])
                ->firstOrFail()
                ->toArray();

            $dataset['id'] = $key;
            
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
            // Common errors
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    public function destroy(Request $request)
    {
        $key = $request->input('rowKey');
        $id = $this->hashids->decode($key);

        $employee = new Employee;
        return $employee->dissolve($id);
    }

    private function addEmployee($inputs)
    {
        // Save the newly created employee into database
        $insert = DB::transaction(function () use($inputs)
        {
            $rawPinCode = random_int(1000, 9999);

            // Save the data into the database
            $model = Employee::create([
                Employee::f_EmpNo       => $inputs['input-id-no'],
                Employee::f_FirstName   => $inputs['input-fname'],
                Employee::f_MiddleName  => $inputs['input-mname'],
                Employee::f_LastName    => $inputs['input-lname'],
                Employee::f_Email       => $inputs['input-email'],
                Employee::f_Contact     => $inputs['input-phone'],
                Employee::f_Role        => $inputs['role'],
                Employee::f_Rank        => $inputs['input-position'],
                Employee::f_Status      => Employee::ON_STATUS_DUTY,
                Employee::f_PINCode     => encrypt($rawPinCode)       // 4-digit PIN
            ]);

            // These data will be returned for displaying purposes.
            // We change the encrypted PIN back to readable string
            // after a successful insert, as it returns the model.
            $model->setAttribute( Employee::f_PINCode, $rawPinCode );

            // This hashed row id can be used both as a record identifier
            // on the frontend, and as a QR Code content
            $hashedRowId  = $this->hashids->encode($model->getAttribute('id'));

            return [
                'modelInstance' => $model,
                'frontEndData'  => [
                    'emp_num'       => $model->getAttribute(Employee::f_EmpNo),
                    'emp_status'    => $model->getAttribute(Employee::f_Status),
                    'id'            => $hashedRowId,
                    'total_lates'   => 0,
                    'total_leave'   => 0,
                    'total_absents' => 0,
                    'empname'       => implode(' ', [
                        $model->getAttribute(Employee::f_LastName).',',
                        $model->getAttribute(Employee::f_FirstName),
                        $model->getAttribute(Employee::f_MiddleName),
                    ])
                ]
            ];
        });

        return $insert;
    }

    private function sendEmployeeQR($modelInstance, bool $saveLocalCopy = false)
    {
        $empNum     = $modelInstance->getAttribute(Employee::f_EmpNo);
        $filename   = "qr_$empNum.png";
        $qrcode     = QRMaker::createFrom($modelInstance->getAttribute('id'), $filename);
        $email      = $modelInstance->getAttribute(Employee::f_Email);

        // If the email was provided, send the qr code via email
        if (!empty($email)) {
            
            // Replace the #recipient# with firstname
            $mailMessage = str_replace(
                // Words to find
                ['#recipient#', '#pin#'],

                // Their replacements
                [
                    $modelInstance->getAttribute(Employee::f_FirstName),
                    $modelInstance->getAttribute(Employee::f_PINCode)
                ],

                // From subject
                Messages::EMAIL_REGISTER_EMPLOYEE
            );

            // Build the email content then send it
            Mail::raw($mailMessage, function ($message) use ($qrcode, $email) {

                // Attach the QR code image into the mail. 
                $message->to($email)->subject(Messages::EMAIL_SUBJECT_QRCODE);
                $message->embed($qrcode['qrcodePath'], 'qrcode.png');
            });
        }

        // If an option to save local copy is true, or...
        // If email was not provided, we must send a download link anyway.
        if ($saveLocalCopy !== false || empty($email)) 
        {
            return [
                'fileName' => $filename,
                'url'      => $qrcode['downloadLink']
            ];
        }

        return [];
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
