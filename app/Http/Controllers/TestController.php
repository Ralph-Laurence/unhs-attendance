<?php

namespace App\Http\Controllers;

use App\Http\Utils\QRMaker;
use Hashids\Hashids;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function index()
    {
        $qrcode = QRMaker::generateTempFile('test');
        // $hash = new Hashids();

        // $testDecode = $hash->decode('vm');

        // return view('tests.test')->with('qrcode', $qrcode)->with('decode', $testDecode[0]);

        // $raw = Str::random(4);
        // $enc = encrypt($raw);
        // $dec = decrypt($enc);

        return view('tests.test')->with('qr', $qrcode); //->with('raw', $raw)->with('enc', $enc)->with('dec', $dec);
    }
}
