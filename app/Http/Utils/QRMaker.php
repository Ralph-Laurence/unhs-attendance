<?php

namespace App\Http\Utils;

use App\Http\Text\Messages;
use App\Models\Employee;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class QRMaker
{
    private static function getRelativePath($filename) 
    {
        return "public/qrcodes/$filename";
    }

    private static function getAbsolutePath($filename) 
    {
        return Storage::path(self::getRelativePath($filename));
    }

    //
    // Create the partial qr code with the frame
    //
    private static function makePartialQRCode(string $content)
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

        return $frame;
    }

    public static function generateAndSendTo(Employee $employee, string $content)
    {
        // Build a filename for the qr code image file, with a format of:
        // qrcode-[employee number]-[employee lastname].png

        $fileName = 'qrcode-'.
            $employee->getAttribute(Employee::f_EmpNo).'-'.
            $employee->getAttribute(Employee::f_LastName).
            '.png';

        // Temporarily save the QR Code to a file.
        $qrCode = self::makePartialQRCode($content);
        $qrpath = Extensions::getQRCode_storagePath($fileName);
       
        $qrCode->save($qrpath);

        Mail::send('emails.resendqrcode', [
            'recipientName' => $employee->getAttribute(Employee::f_FirstName),
            'timestamp'     => date('M. d, Y @g:i a')
        ],
        function($message) use($employee, $qrpath, $fileName) 
        {
            $message->to($employee->getAttribute(Employee::f_Email))
                    ->subject('QR Code Attendance Pass')
                    ->attach($qrpath, [
                        'as'    => $fileName,
                        'mime'  => Constants::MIME_TYPE_PNG
                    ]);
        });
        
        // Delete the temporary QR code
        Storage::delete($qrpath);
    }

    public static function encodeQRCodeToPng(string $content)
    {
        $qrCode = self::makePartialQRCode($content);
        return $qrCode->encode('png');
    }

    /**
     * Generates a QR code file that returns the BASE64 Image string
     * that can be displayed inside an <img>.
     * 
     * When $includeBlob = true, it returns an Object. Otherwise returns base64
     */
    public static function generate(string $content, bool $includeBlob = false)
    {
        $qrcode = self::encodeQRCodeToPng($content);

        // convert the generated qrImage to a data url
        $imageURL = 'data:image/png;base64,' . base64_encode($qrcode);

        if (!$includeBlob)
            return $imageURL;

        // create a blob URL for the image data
        $blob = 'data:application/octet-stream;base64,' . base64_encode($qrcode);

        return [
            'imageUrl' => $imageURL,
            'blobUrl'  => $blob
        ];
    }

    /**
     * Generates a QR code file then returns the path
     */
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

    public static function createFrom(string $content, string $fileName = null) : array
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

        // Generate a downloadable QR code url
        $downloadUrl = '404';

        if (file_exists($path))
            $downloadUrl = route('qr-download', [ $fileName ]);

        return [
            'qrcodePath'    => $path,
            'downloadLink'  => $downloadUrl
        ];
    }
}