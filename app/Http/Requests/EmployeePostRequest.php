<?php

namespace App\Http\Requests;

use App\Http\Utils\Constants;
use App\Http\Utils\RegexPatterns;
use App\Http\Utils\ValidationMessages;
use App\Models\Employee;
use App\Rules\EmployeePositionExists;
use Hashids\Hashids;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $employeeTable  = Employee::getTableName();
        $email_unique   = Rule::unique($employeeTable, Employee::f_Email);
        $empid_unique   = Rule::unique($employeeTable, Employee::f_EmpNo);

        // We should ignore the same employee during uniqueness check.
        // This is how the unique rule behaves. We must chain the
        // ->ignore($id) to exclude the current record.
        if ($this->has('update-key') && $this->filled('update-key'))
        {
            $hashids = new Hashids(
                Employee::HASH_SALT, 
                Employee::MIN_HASH_LENGTH
            );

            $id = $hashids->decode( $this->input('update-key') )[0];

            $email_unique->ignore($id);
            $empid_unique->ignore($id);
        }

        return [
            'input-id-no' => [
                'required',
                'regex:' . RegexPatterns::NUMERIC_DASH,
                $empid_unique
            ],
            'input-email' => [
                'required',
                'email',
                'max:64',
                Rule::unique('users', Employee::f_Email),
                $email_unique
            ],
            'option-save-qr' => [
                'nullable',
                Rule::in( Constants::CHECKBOX_STATES )
            ],            
            'role'           => 'required|in:'           . implode(',', array_keys(Employee::RoleToString)),
            'input-position' => 'required|integer',
            'input-fname'    => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-mname'    => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-lname'    => 'required|max:32|regex:' . RegexPatterns::ALPHA_DASH_DOT_SPACE,
            'input-phone'    => 'nullable|regex:'        . RegexPatterns::MOBILE_NO,
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator) {

            $position = $this->input('input-position');
            $rule     = new EmployeePositionExists( $this->input('role'), $position );

            if (!$rule->passes('input-position', $position))
                $validator->errors()->add('input-position', $rule->message());
        });
    }

    public function messages()
    {
        return [
            'input-id-no.required'      => ValidationMessages::required('ID Number'),
            'input-id-no.regex'         => ValidationMessages::numericDash('ID Number'),
            'input-id-no.unique'        => ValidationMessages::unique('ID Number'),

            'input-fname.required'      => ValidationMessages::required('Firstname'),
            'input-fname.max'           => ValidationMessages::maxLength(32, 'Firstname'),
            'input-fname.regex'         => ValidationMessages::alphaDashDotSpace('Firstname'),

            'input-mname.required'      => ValidationMessages::required('Middlename'),
            'input-mname.max'           => ValidationMessages::maxLength(32, 'Middlename'),
            'input-mname.regex'         => ValidationMessages::alphaDashDotSpace('Middlename'),

            'input-lname.required'      => ValidationMessages::required('Lastname'),
            'input-lname.max'           => ValidationMessages::maxLength(32, 'Lastname'),
            'input-lname.regex'         => ValidationMessages::alphaDashDotSpace('Lastname'),

            'input-email.required'      => ValidationMessages::required('Email'),
            'input-email.unique'        => ValidationMessages::unique('Email'),

            'input-position.required'   => ValidationMessages::option('position'),

            'input-phone.regex'         => "Phone number must be 11 digits and should begin with '09'"
        ];
    }

    /*
    public function validateFields(Request $request, $ignoreId = null)
    {

        $updateId      = $this->input('updateKey');
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
    */
}
