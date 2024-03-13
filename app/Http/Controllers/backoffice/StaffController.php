<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeController;
use App\Http\Utils\RouteNames;
use App\Models\Constants\Staff;
use App\Models\Employee;
use Illuminate\Http\Request;

class StaffController extends EmployeeController
{
    public function index()
    {
        // $routes = [
        //     'defaultDataSource'     => route(RouteNames::Staff['all']),
        //     'POST_Create_Employee'  => route(RouteNames::Staff['create']),
        //     'POST_Update_Employee'  => route(RouteNames::Staff['update']),
        //     'DELETE_Employee'       => route(RouteNames::Staff['destroy']),
        //     'DETAILS_Employee'      => route(RouteNames::Staff['details'])
        // ];

        // $role = Employee::RoleToString[Employee::RoleStaff];

        // return view('backoffice.employees.index')
        //     ->with('requireEmail',      true)           // Require email in registration
        //     ->with('descriptiveRole',   $role)
        //     ->with('routes',            $routes)
        //     ->with('empType',           encrypt($role));



        $routes = [
            'defaultDataSource'     => route(RouteNames::Staff['all']),
            'POST_Create_Employee'  => route(RouteNames::Staff['create']),
            'POST_Update_Employee'  => route(RouteNames::Staff['update']),
            'DELETE_Employee'       => route(RouteNames::Staff['destroy']),
            'DETAILS_Employee'      => route(RouteNames::Staff['show']),

            'actionCreate'          => route(RouteNames::Staff['create']),
            'actionUpdate'          => route(RouteNames::Staff['update']),
            'actionEdit'            => route(RouteNames::Staff['edit'])
        ];

        $roleStr    = Employee::RoleToString[Employee::RoleStaff];
        $positions  = Staff::getRanks(true);

        $modalSetup = [
            'titleAdd'  => "Add new $roleStr",
            'titleEdit' => "Update $roleStr details",
            'icon'      => asset('images/internal/icons/modal_icon_staff.png')
        ];

        return view('backoffice.employees.staff.index')
        ->with('role',              Employee::RoleStaff)
            ->with('modalSetup',        $modalSetup)
            ->with('routes',            $routes)
            ->with('positions',         $positions)
            ->with('empType',           encrypt($roleStr));
    }
    
    public function getStaff() 
    {
        $model = new Employee;
        $data  = $model->getEmployees(Employee::RoleStaff);

        return $data;
    }
}
