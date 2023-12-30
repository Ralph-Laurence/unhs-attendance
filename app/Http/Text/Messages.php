<?php

namespace App\Http\Text;

class Messages
{   
    public const QR_CODE_NOT_RECOGNIZED  = 'The QR code is not recognized by the system. Please try again with a valid QR code.';
    public const QR_CODE_UNREADABLE      = 'The QR code appears to be unreadable, possibly due to corrupted data or physical damage.';

    public const ATTENDANCE_DELETE_OK    = 'Attendance record successfully deleted.';
    public const ATTENDANCE_DELETE_FAIL  = 'Failed to delete attendance record.';
}