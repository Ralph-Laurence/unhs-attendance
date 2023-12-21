<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DailyTimeRecordController extends Controller
{
    public function index()
    {
        return view('backoffice.daily-time-record.index');
    }
}
