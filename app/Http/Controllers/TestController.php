<?php

namespace App\Http\Controllers;

use App\Http\Utils\QRMaker;
use Hashids\Hashids;

class TestController extends Controller
{
    public function index()
    {
        $qrcode = QRMaker::generate('80511');
        $hash = new Hashids();

        $testDecode = $hash->decode('jR');

        return view('tests.test')->with('qrcode', $qrcode)->with('decode', $testDecode[0]);
    }
}
