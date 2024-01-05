<?php

namespace App\Http\Controllers;

use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private Employee $employeeModel;

    public function __construct() {
        $this->employeeModel = new Employee();
    }
    
    public function index()
    {
        $routes = [
            'defaultDataSource' => route(RouteNames::Staff['all'])
        ];

        return view('backoffice.staff.index')
            ->with('routes', $routes);
    }

    public function getStaff() 
    {
        $dataset = $this->employeeModel->getStaff();

        return $dataset;
    }
}
