<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Trail</title>
    <style>
        @page {
            /* margin: 30px 20px 30px 20px !important; top right bottom left */
            size: A4;
            margin: 0 !important;
        }

        @media print {
            html,
            body {
                width: 210mm;
                height: 297mm;
            }
        }

        * { box-sizing: border-box; }
        h5, h4 { margin: 0; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            width: 210mm;
            min-height: 297mm;
            padding: 10px;
            margin: auto;
        }

        /* Utilities */
        .font-14   { font-size: 14px; }
        .uppercase { text-transform: uppercase; }

        .my-3 {
            margin-bottom: 1rem;
            margin-top: 1rem;
        }
        .mt-3 {
            margin-top: 1rem;
        }

        .doc-bullet-diams::before {
            content: "\2756";
            font-style: normal;
        }

        #paper-title,
        #employee-name {
            text-align: center;
            width: 100%;
        }

        .agreement-container,
        .header-container,
        .table-container {
            width: 100%;
        }

        .agreement-container table,
        .header-container table {
            width: calc(50% - 10px);
            /* Subtract half of the gap from each table */
            float: left;
        }

        .table-container table {
            width: calc(50% - 20px);
            /* Subtract half of the gap from each table */
            float: left;
            font-size: 13px;
            border-collapse: collapse;
            table-layout: fixed;
            text-align: center;
        }

        .agreement-container,
        .header-container,
        .table-container {
            width: 100%;
        }

        .agreement-container table,
        .header-container table,
        .table-container table {
            width: calc(50% - 10px);
            /* Subtract half of the gap from each table */
            float: left;
        }

        .agreement-container table {
            table-layout: fixed;
        }

        .table-container table {
            font-size: 13px;
            border-collapse: collapse;
            table-layout: fixed;
            text-align: center;
        }

        .agreement-container table:first-child,
        .header-container table:first-child,
        .table-container table:first-child {
            margin-right: 20px;
            /* Add the gap */
        }

        .table-container table,
        .table-container th,
        .table-container td {
            border: 1px solid black;
            text-align: center;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .table-container table thead .th-normal th {
            font-weight: normal;
        }

        .table-container table thead .th-day {
            width: 36px;
            min-width: 36px;
            max-width: 36px;
        }

        .table-container table thead .th-time {
            width: 55px;
            min-width: 55px;
        }

        .text-primary { color: #473AE4; }
        .text-danger  { color: #FF2641; }
        .text-center  { text-align: center; }
    </style>
</head>

<body>
    <div class="header-container">
        @include('reports.dtr-header')
        @include('reports.dtr-header')
    </div>
    <div class="table-container">
        @include('reports.dtr-table', ['adapter' => $adapter])
        @include('reports.dtr-table', ['adapter' => $adapter])
    </div>
    <div class="agreement-container">
        @include('reports.dtr-agreement')
        @include('reports.dtr-agreement')
    </div>
</body>

</html>