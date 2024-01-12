<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance Trail</title>
    <style>
    @page {
        margin: 30px 20px 30px 20px !important; /* top right bottom left */
    }
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
    }
    table.dataset-table {
        width: 100%;
        border-collapse: collapse;
    }
    table.counters-table 
    {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        margin-top: 20px;
    }
    table.signature-wrapper 
    {
        table-layout: fixed;
        width: 100%;
        margin-top: 60px;
    }
    .signature_space
    {
       width: 50%;
       margin: 0 auto;
       height: 1px;
       background: #dddddd;
    }
    table tbody {
        font-size: 13px;
    }
    table.dataset-table td {
        min-height: 40px;
        height: 40px;
    }

    table.counters-table thead th,
    table.dataset-table thead th {
        vertical-align: middle;
        font-size: 11px;
        text-transform: uppercase;
        opacity: .85;
    }
    /* table.counters-table td:nth-child(even), 
    table.counters-table th:nth-child(even) { */
    table.counters-table th {
        background-color: #EEEEEE;
    }
    td.td-25 {
        width: 25px;
        min-width: 25px;
        max-width: 25px;
    }

    td.td-60 {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
    }

    th.th-w60{
        width: 60px;
        min-width: 60px;
        max-width: 60px;
    }

    th.th-w80,
    td.td-80 {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
    }
    
    th.th-w150,
    td.td-150 {
        width: 150px;
        min-width: 150px;
        max-width: 150px;
    }

    th.th-w200,
    td.td-200 {
        width: 200px;
        min-width: 200px;
        max-width: 200px;
    }

    th.th-h30,
    td.td-h30 {
        height: 30px;
        min-height: 30px;
        max-height: 30px;
    }
    td.darker-text {
       color: #0F0928;
    }
    td.gray-text {
       color: #363636;
    }
    table.counters-table tr,
    table.dataset-table tr {
        border-bottom: solid 1px #d1d1d1;
        vertical-align: middle;
    }
    table.dataset-table tr:nth-child(even) {
        background-color: #eeeeee;
    }
    table.dataset-table tr:nth-child(odd) {
        background-color: #ffffff;
    }
    table.counters-table th,
    table.counters-table td {
        height: 25px;
        min-height: 25px;
        max-height: 25px;
    }
    .border-start {
        border-left: solid 1px #d1d1d1;
    }
    .border-end {
        border-right: solid 1px #d1d1d1;
    }
    .border-top {
        border-top: solid 1px #d1d1d1;
    }
    .text-center {
        text-align: center;
    }
    .text-end {
        text-align: right;
    }
    .td-absent-x-mark {
        color: #FF2641 !important;
        font-size: 18px;
    }
    .td-day {
        color: #3d2da3 !important;
    }
    .opacity-75 {
        opacity: 0.75;
    }
    .header-banner {
        width: 100%;
        margin-bottom: 10px;
    }

    .header-banner .logo-wrapper {
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .centered {
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .header-banner .logo-wrapper td {
        vertical-align: middle;
    }

    .header-banner .logo-wrapper h3 {
        margin-bottom: 4px;
        margin-top: 2px;
        display: inline-block;
    }

    .header-banner .logo-wrapper p {
        margin-top: 0;
        margin-bottom: 4px;
    }

    .paper-title {
        font-size: 18px;
        margin-top: 14px;
        margin-bottom: 4px;
    }
    .report-date {
        margin-bottom: 14px;
        font-size: 13px;
    }
    .text-certify,
    .employee-identity {
        font-size: 13px;
    }

    .employee-identity table {
        width: 100%;
    }
    </style>
</head>
<body>
    <div class="header-banner text-center">
        <table class="logo-wrapper">
            <tbody>
                <tr>
                    <td class="td-60">
                        <img src="{{ public_path('images/internal/templates/pdf-logo.png') }}" alt="logo" width="50" height="50">
                    </td>
                    <td>
                        <h3>{{ $pdf_banner_org_name }}</h3>
                        <p>{{ $pdf_banner_org_addr }}</p> <!-- Replace with your subtitle -->
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="paper-title text-center">
        Attendance Report
    </div>
    <div class="report-date text-center">
        {{ $dateRange }}
    </div>
    <div class="employee-identity">
        <table>
            <tbody>
                <tr>
                    <td class="td-60">
                        <span class="opacity-75">Name:</span>
                    </td>
                    <td class="">{{ $emp_name }}</td>
                    <td class="text-end">Printed on {{ date('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="td-60">
                        <span class="opacity-75">ID No:</span>
                    </td>
                    <td class="">{{ $emp_id }}</td>
                    <td class="text-end"> {{-- date('g:i a') --}}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="dataset-table">
        <thead>
            <tr class="border-top">
                <th class="border-start th-w80" rowspan="2" colspan="2">Day</th>
                <th class="th-w200 th-h30 text-center border-start" colspan="2">AM</th>
                <th class="th-w200 th-h30 text-center border-start border-end" colspan="2">PM</th>
                <th class="border-end th-w80" rowspan="2">Duration</th>
                <th class="border-end th-w80" rowspan="2">Late</th>
                <th class="border-end th-w80" rowspan="2">Under<br>time</th>
                <th class="border-end th-w80" rowspan="2">Over<br>time</th>
                <th class="border-end th-w80" rowspan="2">Status</th>
            </tr>
            <tr>
                <th class="th-h30 text-center border-start">In</th>
                <th class="th-h30 text-center">Out</th>
                <th class="th-h30 text-center border-start">In</th>
                <th class="th-h30 text-center border-end">Out</th>
            </tr>
        </thead>
        <tbody>
            @php
                $countAbsent    = 0;
                $countLate      = 0;
                $countLeave     = 0;
                $countOvertime  = 0;
                $countUndertime = 0;
            @endphp

            {{-- @for($i = 0; $i < 500; $i++) --}}
            @foreach ($dataSet as $data)
                @php
                    $absent_td_mark = '';

                    $except = [
                        $data->day_number,
                        $data->day_name,
                        $data->status
                    ];

                    if (!empty($data->late))
                        $countLate++;
                    
                    if (!empty($data->overtime))
                        $countOvertime++;
                    
                    if (!empty($data->undertime))
                        $countUndertime++;

                    if ($data->status == $stat_absent)
                    {
                        $absent_td_mark  = 'td-absent-x-mark';
                        $data->am_in     = $unicode_x;
                        $data->am_out    = $unicode_x;
                        $data->pm_in     = $unicode_x;
                        $data->pm_out    = $unicode_x;
                        $data->duration  = $unicode_x;
                        $data->late      = $unicode_x;
                        $data->undertime = $unicode_x;
                        $data->overtime  = $unicode_x;

                        $countAbsent++;
                    }

                @endphp
            <tr>
                <td class="border-start td-25 text-center td-day">{{ $data->day_number }}</td>
                <td class="td-45 td-day">{{ $data->day_name }}</td>
                <td class="{{ $absent_td_mark }} border-start text-center darker-text">{{ $data->am_in }}</td>
                <td class="{{ $absent_td_mark }} text-center gray-text">{{ $data->am_out }}</td>
                <td class="{{ $absent_td_mark }} border-start text-center gray-text">{{ $data->pm_in }}</td>
                <td class="{{ $absent_td_mark }} border-end text-center darker-text">{{ $data->pm_out }}</td>
                <td class="{{ $absent_td_mark }} border-end td-80 text-center gray-text">{{ $data->duration }}</td>
                <td class="{{ $absent_td_mark }} border-end td-80 text-center gray-text">{{ $data->late }}</td>
                <td class="{{ $absent_td_mark }} border-end td-80 text-center gray-text">{{ $data->undertime }}</td>
                <td class="{{ $absent_td_mark }} border-end td-80 text-center gray-text">{{ $data->overtime }}</td>
                <td class="border-end td-80 text-center">{{ $data->status }}</td>
            </tr>

            @endforeach
            {{-- <tr>
                <td class="border-start td-25 text-center td-day">123</td>
                <td class="td-45 td-day">Abc</td>
                <td class="border-start text-center darker-text">00:00:00</td>
                <td class="text-center gray-text">00:00:00</td>
                <td class="border-start text-center gray-text">00:00:00</td>
                <td class="border-end text-center darker-text">00:00:00</td>
                <td class="border-end td-80 text-center gray-text">00:00:00</td>
                <td class="border-end td-80 text-center gray-text">00:00:00</td>
                <td class="border-end td-80 text-center gray-text">00:00:00</td>
                <td class="border-end td-80 text-center gray-text">00:00:00</td>
                <td class="border-end td-80 text-center">xyz</td>
            </tr>
            
            @endfor --}}
        </tbody>
    </table>

    <table class="counters-table">
        <thead>
            <tr class="border-top">
                <th class="border-start"></th>
                <th>Late</th>
                <th>Undertime</th>
                <th>Overtime</th>
                <th>Leave</th>
                <th class="border-end">Absence</th>
            </tr>
        </thead>
        <tbody>
            {{-- <tr>
                <td>Count</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr> --}}
            <tr>
                <td class="text-center border-start">Record Count</td>
                <td class="text-center">{{ $countLate }}</td>
                <td class="text-center">{{ $countUndertime }}</td>
                <td class="text-center">{{ $countOvertime }}</td>
                <td class="text-center">{{ $countLeave }}</td>
                <td class="text-center border-end">{{ $countAbsent }}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="text-certify text-center">
        I CERTIFY on my honor that the above is a true and correct report of the hours of work performed. 
        This record was made daily upon my arrival at and departure from the office.
    </div>

    <table class="signature-wrapper">
        <tbody>
            <tr>
                <td class="text-center">
                    <div class="signature_space"></div>
                </td>
                <td class="text-center">
                    <div class="signature_space"></div>
                </td>
            </tr>
            <tr>
                <td class="text-center">Employee's Signature</td>
                <td class="text-center">Verified By</td>
            </tr>
        </tbody>
    </table>
</body>
</html>