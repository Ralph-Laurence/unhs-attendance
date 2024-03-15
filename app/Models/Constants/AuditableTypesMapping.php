<?php

namespace App\Models\Constants;

use App\Models\Employee;
use App\Models\Faculty;
use App\Models\LeaveRequest;
use App\Models\Staff;

// This field mapping feature converts table fields to 
// human-readable equivalent
class AuditableTypesMapping
{
    private $employeeFieldsMap = [
        Employee::f_FirstName   => 'Firstname',
        Employee::f_MiddleName  => 'Middlename',
        Employee::f_LastName    => 'Lastname',
        Employee::f_Contact     => 'Contact No.',
        Employee::f_EmpNo       => 'ID No.',
        Employee::f_Rank        => 'Position',
        Employee::f_Email       => 'Email'
    ];

    private $leaveRequestFieldsMap = [
        LeaveRequest::f_Duration    => 'Duration',
        LeaveRequest::f_EndDate     => 'End Date',
        LeaveRequest::f_StartDate   => 'Start Date',
        LeaveRequest::f_LeaveType   => 'Leave Type',
        LeaveRequest::f_LeaveStatus => 'Status'
    ];

    private $valueToReadable  = [];
    private $auditableMapping = [];

    function __construct()
    {
        $this->auditableMapping = [
            Faculty::class       => $this->employeeFieldsMap,
            Staff::class         => $this->employeeFieldsMap,
            LeaveRequest::class  => $this->leaveRequestFieldsMap
        ];

        // Convert integer values to their descriptive equivalent
        $this->valueToReadable = [
            LeaveRequest::f_LeaveStatus => function($value) 
            {
                $statuses = LeaveRequest::getStatuses();
                return $statuses[ $value ];
            },
            LeaveRequest::f_LeaveType   => function($value)
            {
                $types = LeaveRequest::getTypes();
                return $types[$value];
            }
        ];
    }
    //
    // Convert integer values to their descriptive equivalent
    //
    public function mapValue(string $fieldName, $value, array $customMap = [], bool $keyvaluepair = false)
    {
        // If a custom values mapping was given, use it
        if (!empty($customMap)) 
        {
            // When the custom mapping uses a closure function as its value   
            if (!$keyvaluepair && isset($customMap[$fieldName]))
                return $customMap[$fieldName]($value);

            // When the custom mapping uses a standard Key-Value-Pair array
            if ($keyvaluepair && isset($customMap[$value]))
                return $customMap[$value];
        }

        // Use the default mapping that uses closure
        if (isset($this->valueToReadable[$fieldName]))
            return $this->valueToReadable[$fieldName]($value);

        // When the fieldname isn't present in all mappings,
        // return the original value instead
        return $value;
    }

    //
    // Fix the fieldname to human readable form
    //
    public function mapField(string $auditableName, string $fieldName)
    {
        return $this->auditableMapping[$auditableName][$fieldName];
    }
}
