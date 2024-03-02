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
        return view('tests.test');
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
