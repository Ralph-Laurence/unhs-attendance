<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use App\Http\Utils\Constants;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        $footerText = date('Y') ." ". Constants::OrganizationName;

        return view('scanner.index')
            ->with('footerText', $footerText);
    }
}

// https://www.simplesoftware.io/#/docs/simple-qrcode
// https://github.com/vinkla/hashids