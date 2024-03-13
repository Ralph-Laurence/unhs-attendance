<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>QR Code</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            background-color: #ECECEC;
            color: #363636;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-align: center;
            font-size: 14px;
        }

        .mail-body {
            margin: 20px;
            width: 480px;
            margin: auto;
            box-shadow: 1px 1px 4px #d2d2d2;
        }

        .message {
            text-align: justify;
            text-justify: inter-word;
            background-color: white;
            padding: 14px 20px;
            border: 1px solid #7B76B8;
            border-top: none;
        }

        .big-title {
            font-size: 18px;
            font-weight: 600;
            width: auto;
            background-color: #352e68;
            color: white;
            padding: 8px;
            border-bottom: 2px solid #7D73EC;
        }

        .underline {
            display: inline-block;
            border-bottom: 2px solid #7D73EC;
            color: #352E68;
        }

        .note {
            color: #352E68;
            font-weight: 600;
        }

        ol li {
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .text-center {
            text-align: center;
        }

        .timestamp {
            color: #5a5862;
        }
    </style>
</head>

<body>

    <div class="mail-body">
        <div class="big-title">
            UNHS Attendance Monitoring System
        </div>
        <div class="message">
            <h4 class="subject">QR Code Attendance Pass</h4>
            <p>
                &emsp;&emsp;&emsp;Hello {{ $recipientName }}, attached below is the QR code that will be used for your
                <span class="underline">authentication</span> in our Attendance Monitoring System.

                To use it, simply present the QR code at the QR Code Scanner. This will automatically log your
                attendance in our system.
                <br><br>
                Please keep it secure to prevent it from being lost.<br><br><span class="note">Here are some steps to
                    secure your QR code:</span>
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

</body>

</html>