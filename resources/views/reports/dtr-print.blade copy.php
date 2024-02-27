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
        html, body {
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
    .doc-bullet-diams::before {
        content: "\2756";
        font-style: normal;
    }
    #paper-title, #employee-name {
        text-align: center;
        width: 100%;
    }

    .table-container {
        width: 100%;
    }
    .table-container table {
        width: calc(50% - 5px); /* Subtract half of the gap from each table */
        float: left;
        font-size: 13px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .table-container table:first-child {
        margin-right: 10px; /* Add the gap */
    }

    .table-container table, th, td {
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
    </style>
</head>
<body>
    <h4 id="paper-title" class="uppercase">Daily Time Record</h4>
    <h5 id="employee-name" class="font-14 my-3 uppercase"><u>Employee Name</u></h5>
    <h5 id="employee-name" class="font-14 my-3">
        <i class="doc-bullet-diams"></i>
        <span id="month">For the month of</span>
        <i class="doc-bullet-diams"></i>
    </h5>
    <div class="table-container">
       
    </div>
</body>
</html>