<?php

namespace App\Http\Text;

class Messages
{   
    public const QR_CODE_NOT_RECOGNIZED  = 'The QR code is not recognized by the system. Please try again with a valid QR code.';
    public const QR_CODE_UNREADABLE      = 'The QR code appears to be unreadable, possibly due to corrupted data or physical damage.';

    public const ATTENDANCE_CRED_FAIL    = 'Incorrect credential details!';
    public const ATTENDANCE_CRED_REQUIRE = 'Please enter your credentials!';

    public const ATTENDANCE_DELETE_OK    = 'Attendance record successfully deleted.';
    public const ATTENDANCE_DELETE_FAIL  = 'Failed to delete attendance record.';

    public const GENERIC_DELETE_FAIL     = 'A problem has occurred while trying to delete the record.';
    public const GENERIC_DELETE_OK       = 'Record successfully deleted.';

    public const EMPLOYEE_INEXISTENT     = 'Employee does not exist.';
    public const READ_FAIL_INEXISTENT    = 'It seems the record doesn\'t exist or has already been removed.';
    public const MODIFY_FAIL_INEXISTENT  = 'It seems the record you\'re trying to modify doesn\'t exist or has already been removed.';

    public const TEACHER_DELETE_OK       = 'Faculty member successfully removed from records.';
    public const TEACHER_DELETE_FAIL     = 'Failed to remove faculty member from the records.';

    public const STAFF_DELETE_OK         = 'Employee successfully removed from staff records.';
    public const STAFF_DELETE_FAIL       = 'Failed to remove employee from the staff records.';

    public const EMAIL_REGISTER_EMPLOYEE = "Hello #recipient#, this QR code will be used for your " . 
                                           "authentication in our Attendance Monitoring System.\n\n". 
                                           "To use it, simply present the QR code at the attendance scanner. " . 
                                           "This will automatically log your attendance in our system.\n\n" . 
                                           "In case the QR code is unavailable, you can use your PIN code for authentication.\n\nPIN Code: #pin#";
    
    public const EMAIL_SUBJECT_QRCODE    = 'QR Code for Attendance Monitoring System';

    public const READ_RECORD_FAIL        = 'Unable to read the record. Please try again.';
    public const UPDATE_FAIL_INCOMPLETE  = 'Could not update the record because some information was missing.';
    
    public const UPDATE_FAIL_CANT_IDENTIFY_RECORD = "Failed to update the record because it cannot be identified.";
    public const UPDATE_FAIL_NON_EXISTENT_RECORD  = "Failed to update a non-existent record.";

    public const LEAVE_REQUEST_OVERLAP   = 'Leave request overlaps with an existing one.';
    public const LEAVE_REQUEST_APPROVED  = 'Leave request approved';
    public const LEAVE_REQUEST_REJECTED  = 'Leave request rejected';

    public const PROCESS_REQUEST_FAILED  = "Unable to process your request because of an error.";
    public const REVERT_TRANSACT_ON_FAIL = 'Apologies, we encountered an issue while processing your request and had to revert the changes. Please try again later or contact support if the issue persists.';

    public const END_DATE_LESS_THAN      = 'The end date must not be earlier than the start date.';

    public const SEC_ERR_HASH_ID         = "Oops! It seems like the security key for the record you're trying to access doesn't match. Please try again later.\n\nIf the problem persists, consider reaching out to our support team for help.";
}