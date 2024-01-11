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
     * then returns the path
     */
    public static function generateTempFile(string $content, &$pathToAsset = null, &$downloadUrl = null) : string
    {
        // Load the QR Code frame template image
        $framePath  = public_path('images/internal/templates/qr-frame.png');
        $frame      = Image::make($framePath);

        // Generate a 200x200 QR code image
        $qrcode     = QrCode::create($content);
        $pngWriter  = new PngWriter;

        $qrcode->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh)
                ->setSize(200)
                ->setMargin(3);
        
        $generated  = $pngWriter->write($qrcode);
        $qrImage    = Image::make($generated->getString());
    
        // Overlay the generated qr code in front of the frame
        // positioned at the center
        $frame->insert($qrImage, 'center');

        // Generate a filename and file path
        $fileName = 'temp_qr_' . Str::random(10) . '.png';
        $path = storage_path("app/public/temp/qrcodes/$fileName");

        // Save the generated image into the file path
        $frame->save($path);

        // This will get the readable path of the qr code which
        // will be used to embed as an email attachment
        $pathToAsset = asset("storage/temp/qrcodes/$fileName");

        // Generate a downloadable QR code url
        $downloadUrl = route('qr-download', [ $fileName ]);

        if (!file_exists($path))
            $downloadUrl = '404';

        return $path;
    }

    public static function saveFile(string $content, string $fileName = null, &$pathToAsset = null, &$downloadUrl = null) : string
    {
        // Load the QR Code frame template image
        $framePath  = public_path('images/internal/templates/qr-frame.png');
        $frame      = Image::make($framePath);

        // Generate a 200x200 QR code image
        $qrcode     = QrCode::create($content);
        $pngWriter  = new PngWriter;

        $qrcode->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh)
                ->setSize(200)
                ->setMargin(3);
        
        $generated  = $pngWriter->write($qrcode);
        $qrImage    = Image::make($generated->getString());
    
        // Overlay the generated qr code in front of the frame
        // positioned at the center
        $frame->insert($qrImage, 'center');

        // Generate a filename and file path
        //$fileName = 'temp_qr_' . Str::random(10) . '.png';
        $path = Extensions::getQRCode_storagePath($fileName);

        // Save the generated image into the file path
        $frame->save($path);

        // This will get the readable path of the qr code which
        // will be used to embed as an email attachment
        $pathToAsset = Extensions::getQRCode_assetPath($fileName);

        // Generate a downloadable QR code url
        $downloadUrl = route('qr-download', [ $fileName ]);

        if (!file_exists($path))
            $downloadUrl = '404';

        return $path;
    }
}