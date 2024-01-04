<?php

namespace App\Http\Controllers;

use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class TeachersController extends Controller
{
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
        $dataset = $this->buildQueryGetTeachers()->get();

        Extensions::hashRowIds($dataset);

        return json_encode([
            'data' => $dataset->toArray(),
        ]);
    }

    private function buildQueryGetTeachers()
    {
        $employeeFields = Extensions::prefixArray('e.', [
            Employee::f_EmpNo      . ' as idno',
            Employee::f_FirstName  . ' as fname',
            Employee::f_MiddleName . ' as mname',
            Employee::f_LastName   . ' as lname',
            Employee::f_Position   . ' as role',
        ]);

        //$fields  = array_merge($this->attendanceFields, $this->employeeFields);
        $query = DB::table(Employee::getTableName() . ' as e')
        ->select($employeeFields)
        //->leftJoin(Employee::getTableName() . ' as e', 'e.id', '=', 'a.'.Attendance::f_Emp_FK_ID)
        ->orderBy('e.fname', 'asc');
        
        return $query; 
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