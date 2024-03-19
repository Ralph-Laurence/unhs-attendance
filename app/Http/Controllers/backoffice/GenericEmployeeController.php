<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\QRMaker;
use App\Models\Employee;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenericEmployeeController extends EmployeeControllerBase
{
    protected function Insert(array $data)
    {   
    }

    protected function Delete(int $employeeId)
    {   
    }

    protected function Modify(array $data)
    {
    }

    public function loadEmpNumbers()
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

    public function resendQRCode(Request $request)
    {
        $employeeKey = $request->input('key');
        $id = $this->hashids->decode($employeeKey);

        if (empty($id))
            return Extensions::encodeFailMessage(Messages::CANT_PERFORM_ACTION);

        try 
        {
            $employee = Employee::select([
                Employee::f_EmpNo,
                Employee::f_PINCode,
                Employee::f_LastName,
                Employee::f_FirstName,
                Employee::f_Email
            ])
            ->findOrFail($id[0]);

            QRMaker::resendTo($employee, $employeeKey);
            
            return Extensions::encodeSuccessMessage(Messages::EMPLOYEE_QR_SENT_OK);
        } 
        catch (ModelNotFoundException $ex)
        {
            return Extensions::encodeFailMessage(Messages::EMPLOYEE_INEXISTENT);
        }
        catch (Exception $ex) 
        {
            return Extensions::encodeFailMessage(Messages::EMPLOYEE_QR_SEND_FAIL);
        }
    }

    public function listEmployeeNos(Request $request)
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
            $f_empNo . ' as label',

            // Concatenate the names then do not include spaces if mname is empty
            DB::raw("CONCAT_WS(' ', $lname, ',', $fname, NULLIF($mname, '')) as value")
        ])
        ->get()
        ->toArray();

        return response()->json([
            'code' => Constants::XHR_STAT_OK,
            'data' => $empIds
        ]);
    }
}
