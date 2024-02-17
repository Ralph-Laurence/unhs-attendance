<?php

namespace App\Http\Controllers;

use App\Http\Utils\QRMaker;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Hashids\Hashids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestController extends Controller
{
    private $emp_hashids;

    public function __construct() 
    {
        $this->emp_hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    }

    public function index()
    {
        /**
         * When you use with('relation') without has('relation'), it behaves like a SQL LEFT JOIN. 
         * It retrieves all records from the first (or “left”) table, and the matched records from 
         * the second (or “right”) table. If there is no match, the result is NULL on the right side.
         * 
         * When you chain "::has('relation')" into "::with('relation')", it behaves more like a 
         * SQL INNER JOIN. It only retrieves records where there is a match in both tables.
         * 
         * The foreign key must also be included for this to work. In this case, we limit only the
         * selection of necessary fields to reduce server stress.
         */
        $employees = Employee::whereHas('leave_requests', function($query)
        {
            $query->where(LeaveRequest::f_LeaveStatus, '=', LeaveRequest::LEAVE_STATUS_APPROVED);
        })
        ->with(['leave_requests' => function ($query) {
            $query->select([
                'id',
                LeaveRequest::f_Emp_FK_ID,
                LeaveRequest::f_StartDate,
                LeaveRequest::f_EndDate,
                LeaveRequest::f_LeaveStatus
            ])
            ->where(LeaveRequest::f_LeaveStatus, '=', LeaveRequest::LEAVE_STATUS_APPROVED);
        }])
        ->get();
        

        // Full LEFT JOIN:
        // $employees = Employee::with('leave_requests')->get();

        return view('tests.test')->with('employees', $employees);
    }

    public function pinsamples()
    {
        $dataset = Employee::select([
            Employee::f_EmpNo . ' as empno',
            Employee::f_PINCode . ' as pin'
        ])
        ->get()
        ->toArray();

        return view('tests.pin-test')->with('dataset', $dataset);
    }

    public function qrsamples() 
    {
        $emp = Employee::select([
            'id',
            'firstname as fname',
            'middlename as mname',
            'lastname as lname',
            'emp_no'
        ])->get();

        $data = [];

        foreach ($emp as $e)
        {
            $id = $this->emp_hashids->encode($e->id);
            $qrcode = QRMaker::generate($id);
            
            $data[$e->emp_no] = [
                'qrcode' => $qrcode,
                'name'   => implode(' ', [ $e->fname, $e->mname, $e->lname ])
            ]; 
        }

        return view('tests.qr-test')->with('codes', $data);
    }
}
