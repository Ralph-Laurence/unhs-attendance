<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeController;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\Constants\Faculty;

class TeachersController extends EmployeeController
{
    public function index()
    {
        $routes = [
            'defaultDataSource'     => route(RouteNames::Teachers['all']),
            'POST_Create_Employee'  => route(RouteNames::Teachers['create']),
            'POST_Update_Employee'  => route(RouteNames::Teachers['update']),
            'DELETE_Employee'       => route(RouteNames::Teachers['destroy']),
            'DETAILS_Employee'      => route(RouteNames::Teachers['details']),

            'actionCreate'          => route(RouteNames::Teachers['create']),
            'actionUpdate'          => route(RouteNames::Teachers['update']),
            'actionEdit'            => route(RouteNames::Teachers['edit'])
        ];

        $roleStr    = Employee::RoleToString[Employee::RoleTeacher];
        $positions  = Faculty::getRanks(true);

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
}
