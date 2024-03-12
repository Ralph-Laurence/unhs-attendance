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
