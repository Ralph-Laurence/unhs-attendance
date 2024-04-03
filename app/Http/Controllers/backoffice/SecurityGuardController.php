<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\backoffice\EmployeeControllerBase;
use App\Http\Utils\RouteNames;
use App\Models\Constants\StaffConstants;
use App\Models\Employee;
use App\Models\SecurityGuard;
use App\Models\Staff;
use Illuminate\Http\Request;

class SecurityGuardController extends EmployeeControllerBase
{
    public function index()
    {
        $routes = [
            'defaultDataSource'     => route(RouteNames::Guards['all']),
            'DETAILS_Employee'      => route(RouteNames::Guards['show']),

            'actionCreate'          => route(RouteNames::Guards['create']),
            'actionUpdate'          => route(RouteNames::Guards['update']),
            'actionEdit'            => route(RouteNames::Guards['edit']),
            'actionDelete'          => route(RouteNames::Guards['destroy']),
        ];

        $roleStr    = Staff::RoleToString[Employee::RoleGuard];
        $positions  = StaffConstants::getRanks(true);

        $modalSetup = [
            'titleAdd'  => "Add new $roleStr",
            'titleEdit' => "Update $roleStr details",
            'icon'      => asset('images/internal/icons/modal_icon_security_guard.png'),

            'usesDefaultPosition' => true,
            'defaultPosition'     => [
                'label' => Staff::STR_ROLE_GUARD,
                'value' => StaffConstants::SECURITY_GUARD
            ]
        ];

        return view('backoffice.employees.guards.index')
            ->with('role',              Employee::RoleGuard)
            ->with('modalSetup',        $modalSetup)
            ->with('routes',            $routes)
            ->with('positions',         $positions)
            ->with('empType',           encrypt($roleStr));
    }
    
    public function getGuards() 
    {
        $model = new Employee;
        $data  = $model->getEmployees(Employee::RoleGuard);

        return $data;
    }

    protected function Delete(int $employeeId) 
    {
        $staff = new SecurityGuard;
        return $staff->dissolve($employeeId);
    }

    protected function Insert(array $data)
    {
        $staff = new SecurityGuard;
        return $staff->insert($data);
    }

    protected function Modify(array $data)
    {
        $staff = new SecurityGuard;
        return $staff->modify($data);
    }
}
