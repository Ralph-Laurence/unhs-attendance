<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePostRequest;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Models\Employee;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//
// Abstract Members will be Captilized to prevent confusion
//
abstract class EmployeeControllerBase extends Controller
{
    protected abstract function Delete(int $employeeId);
    protected abstract function Insert(array $data);
    protected abstract function Modify(array $data);

    protected $hashids;

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

    public function destroy(Request $request)
    {
        $key = $request->input('rowKey');
        $id = $this->hashids->decode($key);

        if (empty($id))
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INCOMPLETE);

        return $this->Delete($id[0]);
    }

    public function store(EmployeePostRequest $request)
    {
        $inputs = $request->validated();
        $rawPin = random_int(1000, 9999);

        $data = [
            'insertData' => [
                Employee::f_EmpNo       => $inputs['input-id-no'],
                Employee::f_FirstName   => $inputs['input-fname'],
                Employee::f_MiddleName  => $inputs['input-mname'],
                Employee::f_LastName    => $inputs['input-lname'],
                Employee::f_Email       => $inputs['input-email'],
                Employee::f_Contact     => $inputs['input-phone'],
                Employee::f_Role        => $inputs['role'],
                Employee::f_Rank        => $inputs['input-position'],
                Employee::f_Status      => Employee::ON_STATUS_DUTY,
                Employee::f_PINCode     => encrypt($rawPin)
            ],
            'extraData' => [
                'rawPinCode' => $rawPin,
                'saveQRCode' => ($inputs['option-save-qr'] == Constants::CHECKBOX_ON)
            ]
        ];

        return $this->Insert($data);        
    }

    // Performs a database update
    public function update(EmployeePostRequest $request)
    {
        $inputs = $request->validated();
        // error_log(print_r($inputs, true));

        // return;
        $data = [
            'updateData' => [
                Employee::f_EmpNo       => $inputs['input-id-no'],
                Employee::f_FirstName   => $inputs['input-fname'],
                Employee::f_MiddleName  => $inputs['input-mname'],
                Employee::f_LastName    => $inputs['input-lname'],
                Employee::f_Email       => $inputs['input-email'],
                Employee::f_Contact     => $inputs['input-phone'],
                Employee::f_Rank        => $inputs['input-position']
    
            ],
            'extraData' => [
                'updateKey' => $inputs['update-key']
            ]
        ];

       return $this->Modify($data);
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
}
