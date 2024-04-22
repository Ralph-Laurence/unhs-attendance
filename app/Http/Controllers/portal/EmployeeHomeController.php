<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeHomeController extends Controller
{
    public function index()
    {
        return view('portal.home.index');
    }
}
