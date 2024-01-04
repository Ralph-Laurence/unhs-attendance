<?php

namespace App\Http\Controllers;

use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class TeachersController extends Controller
{
    private Employee $employeeModel;

    public function __construct() {
        $this->employeeModel = new Employee();
    }

    public function index()
    {
        $routes = [
            'defaultDataSource' => route(RouteNames::Teachers['all'])
        ];

        return view('backoffice.teachers.index')
            ->with('routes', $routes);
    }

    public function getTeachers() 
    {
        $dataset = $this->employeeModel->getTeachers();

        return $dataset;
    }
}
/** 
SELECT
	CONCAT(e.firstname,' ', e.middlename,' ', e.lastname) as 'name',
    CASE 
    	WHEN (a.status is NULL) then 'absent' 
        else a.status
    END as 'status'
from employees as e
left join attendances as a on a.emp_fk_id = e.id
*/