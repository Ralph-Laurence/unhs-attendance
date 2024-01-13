<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeController;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\Http\Request;

class TeachersController extends EmployeeController
{
    public function index()
    {
        $routes = [
            'defaultDataSource'     => route(RouteNames::Teachers['all']),
            'POST_Create_Employee'  => route(RouteNames::Teachers['create']),
            'POST_Update_Employee'  => route(RouteNames::Teachers['update']),
            'DELETE_Employee'       => route(RouteNames::Teachers['destroy']),
            'DETAILS_Employee'      => route(RouteNames::Teachers['details'])
        ];

        $role = Employee::RoleToString[Employee::RoleTeacher];

        return view('backoffice.employees.index')
            ->with('requireEmail',      true)           // Require email in registration
            ->with('descriptiveRole',   $role)
            ->with('routes',            $routes)
            ->with('empType',           encrypt($role));
    }

    public function getTeachers() 
    {
        $model = new Employee;

        $dataset = $model->getTeachers();

        return $dataset;
    }
}
