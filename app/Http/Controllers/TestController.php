<?php

namespace App\Http\Controllers;

use App\Http\Utils\QRMaker;
use App\Models\Attendance;
use App\Models\Employee;
use Hashids\Hashids;
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
        $dataset = Attendance::where(Attendance::f_Emp_FK_ID, '=', 1)
        ->select([
            Attendance::f_TimeIn      . ' as am_in',
            Attendance::f_LunchStart  . ' as am_out',
            Attendance::f_LunchEnd    . ' as pm_in',
            Attendance::f_TimeOut     . ' as pm_out',
            Attendance::f_Duration    . ' as duration',
            Attendance::f_Late        . ' as late',
            Attendance::f_UnderTime   . ' as undertime',
            Attendance::f_OverTime    . ' as overtime',
            Attendance::f_Status      . ' as status',
        ])
            ->get();

        return view('tests.test')->with('dataset', $dataset);
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
