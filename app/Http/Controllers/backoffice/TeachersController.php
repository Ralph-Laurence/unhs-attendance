<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeControllerBase;
use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\Constants\FacultyConstants;
use App\Models\Faculty;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TeachersController extends EmployeeControllerBase
{
    public function index()
    {
        $routes = [
            'defaultDataSource'     => route(RouteNames::Faculty['all']),
            'POST_Create_Employee'  => route(RouteNames::Faculty['create']),
            'POST_Update_Employee'  => route(RouteNames::Faculty['update']),
            'DELETE_Employee'       => route(RouteNames::Faculty['destroy']),
            'DETAILS_Employee'      => route(RouteNames::Faculty['show']),

            'actionCreate'          => route(RouteNames::Faculty['create']),
            'actionUpdate'          => route(RouteNames::Faculty['update']),
            'actionEdit'            => route(RouteNames::Faculty['edit'])
        ];

        $roleStr    = Employee::RoleToString[Employee::RoleTeacher];
        $positions  = FacultyConstants::getRanks(true);

        $modalSetup = [
            'titleAdd'  => "Add new $roleStr",
            'titleEdit' => "Update $roleStr details",
            'icon'      => asset('images/internal/icons/modal_icon_teacher.png')
        ];

        return view('backoffice.employees.faculty.index')
            ->with('requireEmail',      true)           // Require email in registration
            ->with('role',              Employee::RoleTeacher)
            ->with('modalSetup',        $modalSetup)
            ->with('routes',            $routes)
            ->with('positions',         $positions)
            ->with('empType',           encrypt($roleStr));
    }

    public function getTeachers() 
    {
        $model = new Employee;
        $data  = $model->getEmployees(Employee::RoleTeacher);

        return $data;
    }

    protected function Delete(int $employeeId) 
    {
        $faculty = new Faculty;
        return $faculty->dissolve($employeeId);
    }

    protected function Insert(array $data)
    {
        $faculty = new Faculty;
        return $faculty->insert($data);
    }
}
