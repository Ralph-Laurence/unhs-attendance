<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeController;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\Http\Request;

class StaffController extends EmployeeController
{
    public function index()
    {
        $routes = [
            'defaultDataSource'     => route(RouteNames::Staff['all']),
            'POST_Create_Employee'  => route(RouteNames::Staff['create']),
            'POST_Update_Employee'  => route(RouteNames::Staff['update']),
            'DELETE_Employee'       => route(RouteNames::Staff['destroy']),
            'DETAILS_Employee'      => route(RouteNames::Staff['details'])
        ];

        $role = Employee::RoleToString[Employee::RoleStaff];

        return view('backoffice.employees.index')
            ->with('requireEmail',      true)           // Require email in registration
            ->with('descriptiveRole',   $role)
            ->with('routes',            $routes)
            ->with('empType',           encrypt($role));
    }
    
    public function getStaff() 
    {
        $model = new Employee;
        $data  = $model->getEmployees(Employee::RoleStaff);

        return $data;
    }
}
