<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
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
        $counts = DB::table(Employee::getTableName())
            ->select(Employee::f_Role . ' as role', DB::raw('count(*) as total'))
            ->groupBy(Employee::f_Role)
            ->get()
            ->toArray();

        $roles = Employee::RoleToString;
        
        $dataset = [];
        
        foreach ($counts as $row)
        {
            $roleStr = $roles[ $row->role ];
            $dataset[$roleStr] = $row->total;
        }
        
        return response()->json([
            'data' => $dataset
        ]);
    }
}
