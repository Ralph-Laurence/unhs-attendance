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
    @yield('content')
</body>

</html>