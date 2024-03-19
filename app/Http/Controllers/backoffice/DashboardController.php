<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $routes = [
            'employeeCompare' => route(RouteNames::Dashboard['countEmp'])
        ];

        return view('backoffice.dashboard.index')
            ->with('routes', $routes);
    }

    public function getEmpGraphings(Request $request)
    {
        return response()->json([
            'employeeDifference'    => $this->countEmployeesDifference(),
            'empStatusDifference'   => $this->countEmpStatusDifference(),
            'leaveStatusDifference' => $this->countLeaveStatusDifference()
        ]);
    }

    private function countEmployeesDifference()
    {
        $roles = array_flip(Employee::getRoles());

        $counts = collect($roles)->mapWithKeys(function ($role, $roleId) 
        {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Role, $roleId)
                ->count();
        
            return [$role => $count];
        })->toArray();
        
        return $counts;
    }

    private function countEmpStatusDifference()
    {
        $statuses = [Employee::ON_STATUS_DUTY, Employee::ON_STATUS_LEAVE];

        $counts = collect($statuses)->mapWithKeys(function ($status) {
            $count = DB::table(Employee::getTableName())
                ->where(Employee::f_Status, $status)
                ->count();
        
            return [$status => $count];
        })->toArray();
        
        return $counts;
    }

    private function countLeaveStatusDifference()
    {
        $statuses = LeaveRequest::getStatuses();

        $counts = collect($statuses)->mapWithKeys(function ($status, $statusId) 
        {
            $count = DB::table(LeaveRequest::getTableName())
                ->where(LeaveRequest::f_LeaveStatus, $statusId)
                ->count();
        
            return [$status => $count];
        })->toArray();
        
        return $counts;
    }
}
