@extends('emails.qrcodemail_base')
@section('content')
<div class="mail-body">
    <div class="big-title text-center">
        UNHS Attendance Monitoring System
    </div>
    <div class="message">
        <h4 class="subject">QR Code Attendance Pass</h4>
        <p>
            &emsp;&emsp;&emsp;Hello {{ $recipientName }}, attached below is the QR code that will be used for your
            <span class="underline">authentication</span> in our Attendance Monitoring System.

            To use it, simply present the QR code at the Scanner. This will automatically log your
            attendance in our system.
            <br><br>
            Just in case you misplaced your QR Code, you may still log your attendance in to the system using your PIN Code. You must
            not share this with anyone else.<br><br><b>PIN: <span class="underline">{{ $pin }}</span></b>
            <br><br><span class="note">Here are some steps to secure your QR code:</span>
        </p>
        <ol type="1">
            <li> Store it in a secure location on your device or</li>
            <li>Print it out and keep it in a safe place.</li>
            <li>Do not share your QR code with others unless necessary.</li>
        </ol>
        <h5 class="text-center">
            <i>If you lose your QR code, you may request a new one from the admin.</i>
        </h5>
        <p>Have a nice day!</p>
        <br>
        <h5 class="text-center">
            This is a system generated message, please do not reply.<br>
        </h5>
        <h5 class="timestamp text-center">{{ $timestamp }}</h5>
    </div>
</div>
@endsection