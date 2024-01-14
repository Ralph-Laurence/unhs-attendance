<?php

namespace App\Http\Utils;

class Constants
{
    public const OrganizationName       = 'Uddiawan National High School';
    public const OrgNameShort           = 'UNHS';
    public const OrganizationAddress    = 'Solano, Nueva Vizcaya';
    public const SystemName             = 'Attendance Monitoring System';
    public const BuildVersion           = 'Alpha v1.0.0';

    public const BasicTimeFormat        = 'g:i A';
    public const TimestampFormat        = 'Y-m-d H:i:s';

    public const ValidationStat_Failed  = 400;
    public const ValidationStat_Success = 200;
    public const RecordId_Empty         = 410;
    public const RecordNotFound         = 405;
    public const InternalServerError    = 500;

    public const AttendancePageTitle    = 'Attendance';

    public const XHR_STAT_OK    = 0;
    public const XHR_STAT_FAIL  = -1;
}