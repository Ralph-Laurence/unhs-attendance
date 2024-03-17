<?php

// Traits work like Partial Classes.
// We store all value mapping functions inside this trait
namespace App\Models\Traits;

use App\Models\Constants\FacultyConstants;
use App\Models\Constants\StaffConstants;
use App\Models\Employee;
use Illuminate\Support\Arr;

trait EmployeesAudit
{
    private $transformKeys = [
        Employee::f_FirstName   => 'Firstname',
        Employee::f_MiddleName  => 'Middlename',
        Employee::f_LastName    => 'Lastname',
        Employee::f_EmpNo       => 'ID No',
        Employee::f_Email       => 'Email',
        Employee::f_Contact     => 'Phone',
        Employee::f_Rank        => 'Position',
        Employee::f_Role        => 'Role',
        Employee::f_Status      => 'Status'
    ];

    private $roleMappings = [];
    private $rankMappings = [];

    private function mapRanks($rank)
    {
        if ( array_key_exists($rank, $this->rankMappings) )
            return $this->rankMappings[$rank];

        return $rank;
    }

    private function mapRoles($role)
    {
        if ( array_key_exists($role, $this->roleMappings) )
            return $this->roleMappings[$role];

        return $role;
    }

    private function initValueMappings()
    {
        if (empty($this->rankMappings) && empty($this->roleMappings)) 
        {
            $this->rankMappings = FacultyConstants::getRanks() + StaffConstants::getRanks();
            $this->roleMappings = [
                Employee::RoleTeacher   => Employee::STR_ROLE_TEACHER,
                Employee::RoleStaff     => Employee::STR_ROLE_STAFF
            ];
        }
    }

    private function updateValues(&$data, $key, $oldOrNew)
    {
        if (Arr::has($data, "$oldOrNew.$key")) 
        {
            // Get and remove old pair from the array
            $value = Arr::pull($data, "$oldOrNew.$key");

            switch($key)
            {
                case Employee::f_Rank:
                    $value = $this->mapRanks($value);
                    break;

                case Employee::f_Role:
                    $value = $this->mapRoles($value);
                    break;
            }
            
            // Add new pair with the old value
            Arr::set($data, "$oldOrNew.{$this->transformKeys[$key]}", $value);
        }
    }

    // $data comes from the transformAudit($data) override function.
    public function beautifyTransforms(&$data)
    {
        $this->initValueMappings();

        foreach($this->transformKeys as $k => $v)
        {
            $this->updateValues($data, $k, 'old_values');
            $this->updateValues($data, $k, 'new_values');
        }
    }
}