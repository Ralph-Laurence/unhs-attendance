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
    public const DateFormat             = 'Y-m-d';
    public const ZERO_DURATION          = '00:00:00';

    public const ValidationStat_Failed  = 422;
    public const ValidationStat_Success = 200;
    public const RecordId_Empty         = 410;
    public const RecordNotFound         = 405;
    public const InternalServerError    = 500;

    public const AttendancePageTitle    = 'Attendance';
    public const FLAG_ON  = 'on';
    public const FLAG_OFF = 'off';
    public const FLAG_2FA = '2fa';

    public const XHR_STAT_OK    = 0;
    public const XHR_STAT_FAIL  = -1;

    public const RECORD_FILTER_ALL = 'All';
    public const FILTER_VALUE_ALL  = 0;

    public const NAME_STYLE_EASTERN = 'eastern';    // Family name first
    public const NAME_STYLE_WESTERN = 'western';    // Given name goes first

    public const TABLE_ABSENCE          = 'absence';
    public const TABLE_ATTENDANCES      = 'attendances';
    public const TABLE_LEAVE_REQUESTS   = 'leave_requests';
    public const TABLE_EMPLOYEES        = 'employees';
    public const TABLE_AUDIT_TRAILS     = 'audits';

    public const AUDIT_EVENT_CREATE     = 'create';
    public const AUDIT_EVENT_UPDATE     = 'update';
    public const AUDIT_EVENT_DELETE     = 'delete';

    public const CHECKBOX_ON        = 'on';
    public const CHECKBOX_OFF       = 'off';
    public const CHECKBOX_STATES    = [
        self::CHECKBOX_ON,
        self::CHECKBOX_OFF
    ];

    public const CTYPE_VOWEL     = 1;
    public const CTYPE_CONSONANT = 2;

    public const MIME_TYPE_PNG = 'image/png';
}