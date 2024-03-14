<?php

namespace App\Rules;

use App\Models\Constants\FacultyConstants;
use App\Models\Constants\StaffConstants;
use App\Models\Employee;
use Illuminate\Contracts\Validation\Rule;

class EmployeePositionExists implements Rule
{
    protected $role;
    protected $position;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($role, $position)
    {
        $this->role     = $role;
        $this->position = $position; 
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        switch ($this->role)
        {
            case Employee::RoleTeacher:
                
                $teacherPositions = array_keys( FacultyConstants::getRanks() );
                
                if (!in_array($this->position, $teacherPositions))
                    return false;

                break;

            case Employee::RoleStaff:

                $staffPositions = array_keys( StaffConstants::getRanks() );
                
                if (!in_array($this->position, $staffPositions))
                    return false;

                break;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        //return "Please select a position.";
    }
}
