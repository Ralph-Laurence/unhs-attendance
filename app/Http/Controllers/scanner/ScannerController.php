<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use App\Http\Utils\Constants;
use App\Http\Utils\RouteNames;
use Hashids\Hashids;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    private const QR_STAT_OK    = 0;
    private const QR_STAT_FAIL  = -1;

    public function index()
    {
        $layoutTitles = [
            'system'    => Constants::SystemName,
            'header'    => Constants::OrganizationName,
            'footer'    => date('Y') ." ". Constants::OrganizationName,
            'version'   => Constants::BuildVersion
        ];

        return view('scanner.index')
            ->with('layoutTitles', $layoutTitles)
            ->with('scannerPostURL', route(RouteNames::Scanner['decode']));
    }

    /**
     * The QR codes contain a HASHED data which are the
     * database ids. We need to decode those data
     * and process it for attendance
     */
    public function decode(Request $request)
    {
        $hash = $request->input('hash');
        $hashids = new Hashids();
        $decode  = $hashids->decode($hash);

        if (is_null($decode) || empty($decode))
        {
            return json_encode([
                'status'    => self::QR_STAT_FAIL,
                'message'   => 'Unreadable QR Code'
            ]);    
        }

        return json_encode([
            'status'    => '200',
            'message'   => $decode
        ]);
    }
}

// https://www.simplesoftware.io/#/docs/simple-qrcode
// https://github.com/vinkla/hashids