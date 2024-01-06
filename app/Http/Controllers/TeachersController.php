<?php

namespace App\Http\Controllers;

use App\Http\Utils\Extensions;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\RouteNames;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeachersController extends Controller
{
    private Employee $employeeModel;
    private $hashids;

    public function __construct() 
    {
        $this->employeeModel = new Employee();

        $this->hashids = new Hashids(Employee::HASH_SALT, 10);
    }

    public function index()
    {
        $routes = [
            'defaultDataSource'  => route(RouteNames::Teachers['all']),
            'POST_CreateTeacher' => route(RouteNames::Teachers['create'])
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
            $insert = DB::transaction(function () use ($data) 
            {
                return Employee::create($data);
            });

            $employeeData = $insert->toArray();

            return Extensions::encodeSuccessMessage("Success!", [
                'emp_num'       => $employeeData[Employee::f_EmpNo],
                'fname'         => $employeeData[Employee::f_FirstName],
                'mname'         => $employeeData[Employee::f_MiddleName],
                'lname'         => $employeeData[Employee::f_LastName],
                'emp_status'    => $employeeData[Employee::f_Status],
                'total_lates'   => 0,
                'total_leave'   => 0,
                'total_absents' => 0,
                'id'            => $this->hashids->encode($employeeData['id'])
            ]);
        } 
        catch (\Exception $ex) 
        {    
            return Extensions::encodeFailMessage("Failed " . $ex->getMessage());
        }

        return json_encode($input);
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
            'input-id-no.required'  => ValidationMessages::required('ID Number'),
            'input-id-no.regex'  => ValidationMessages::numericDash('ID Number'),

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

        $validationFields = array(
            'input-id-no'   => 'required|regex:'        . RegexPatterns::NUMERIC_DASH,
            'input-fname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-mname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-lname'   => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-email'   => 'required|max:64',
            'input-contact' => 'nullable|regex:'        . RegexPatterns::MOBILE_NO,
        );

        // Common validation
        $validator = Validator::make($request->all(), $validationFields, $validationMessages);

        if ($validator->fails())
            //return redirect()->back()->withErrors($validator)->withInput();
            //return response()->json(['errors' => $validator->errors()], 400);
            return ['validation_stat' => 400] + ['errors' => $validator->errors()];
        
        $inputData = ['validation_stat' => 200] + ['errors' => $validator->validated()] + $validator->validated();

        return $inputData;
    }
}