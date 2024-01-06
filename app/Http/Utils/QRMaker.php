<?php

namespace App\Http\Utils;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;

class QRMaker
{
    public static function generate(string $content)
    {
        $framePath  = public_path('images/internal/templates/qr-frame.png');
        $frame      = Image::make($framePath);

        $qrcode     = QrCode::create($content);
        $pngWriter  = new PngWriter;

        $qrcode->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh)
                ->setSize(200)
                ->setMargin(3);

        $generated  = $pngWriter->write($qrcode);
        $qrImage    = Image::make($generated->getString());

        $frame->insert($qrImage, 'center');

        // convert the generated qrImage to a data url
        $imageURL = 'data:image/png;base64,' . base64_encode($frame->encode('png'));

        return $imageURL;
    }

    /**
     * Generates a QR code file into temporary directory
     */
    public static function generateTempFile(string $content) : string
    {
        $framePath  = public_path('images/internal/templates/qr-frame.png');
        $frame      = Image::make($framePath);

        $qrcode     = QrCode::create($content);
        $pngWriter  = new PngWriter;

        $qrcode->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh)
                ->setSize(200)
                ->setMargin(3);
        
        $generated  = $pngWriter->write($qrcode);
        $qrImage    = Image::make($generated->getString());
    
        $frame->insert($qrImage, 'center');

        $fileName = 'temp_qr_' . Str::random(10) . '.png';
        $path = storage_path("app/public/temp/qrcodes/$fileName");

        $frame->save($path);

        return $path;
    }
}