<?php

namespace App\Http\Text;

class Messages
{   
    public const QR_CODE_NOT_RECOGNIZED  = 'The QR code is not recognized by the system. Please try again with a valid QR code.';
    public const QR_CODE_UNREADABLE      = 'The QR code appears to be unreadable, possibly due to corrupted data or physical damage.';

    public const ATTENDANCE_DELETE_OK    = 'Attendance record successfully deleted.';
    public const ATTENDANCE_DELETE_FAIL  = 'Failed to delete attendance record.';

    public const TEACHER_DELETE_OK       = 'Faculty member successfully removed from records.';
    public const TEACHER_DELETE_FAIL     = 'Failed to remove faculty member from the records.';

    public const EMAIL_REGISTER_EMPLOYEE = 
    "Hello #recipient#, this QR code will be used for your authentication in our Attendance Monitoring System.\n\nTo use it, simply present the QR code at the attendance scanner. This will automatically log your attendance in our system.";
    // 
    // \n\n For added security, we also offer the option to enable 2-factor authentication. By setting a PIN number, you can ensure that your attendance record is doubly secure. To enable this feature, please visit the link we have provided in this email.
    public const EMAIL_SUBJECT_QRCODE    = 'QR Code for Attendance Monitoring System';
}