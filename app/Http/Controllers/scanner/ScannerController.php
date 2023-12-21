<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner.index');
    }
}
