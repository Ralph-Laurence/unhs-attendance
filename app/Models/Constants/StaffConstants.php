<?php
// Not a Model, but a class that holds constant values
namespace App\Models\Constants;

class StaffConstants extends EmployeeBase
{
    public const GENERIC_STAFF      = 200;

    public const NURSE1             = 201;
    public const NURSE2             = 202;
    public const NURSE3             = 203;
    public const NURSE4             = 204;
    public const NURSE5             = 205;
    public const NURSE6             = 206;
    public const NURSE7             = 207;

    public const LIBRARIAN1         = 208;
    public const LIBRARIAN2         = 209;
    public const LIBRARIAN3         = 210;

    public const ADMIN_AIDE1        = 211;
    public const ADMIN_AIDE2        = 212;
    public const ADMIN_AIDE3        = 213;
    public const ADMIN_AIDE4        = 214;
    public const ADMIN_AIDE5        = 215;
    public const ADMIN_AIDE6        = 216;
    public const ADMIN_AIDE7        = 217;
    
    public const ADMIN_ASST1        = 218;
    public const ADMIN_ASST2        = 219;
    public const ADMIN_ASST3        = 220;
    public const ADMIN_ASST4        = 221;
    public const ADMIN_ASST5        = 222;
    public const ADMIN_ASST6        = 223;
    public const ADMIN_ASST7        = 224;

    public const ADMIN_OFFICER1     = 225;
    public const ADMIN_OFFICER2     = 226;
    public const ADMIN_OFFICER3     = 227;
    public const ADMIN_OFFICER4     = 228;
    public const ADMIN_OFFICER5     = 229;

    public const SECURITY_GUARD     = 230;

    public const JANITOR            = 231;
    public const UTILITY1           = 232;

    // DESCRIPTIVE POSITIONS
    public static function getRanks(bool $flip = false) : array
    {
        $ranks = [
            self::GENERIC_STAFF     => 'Staff',

            self::NURSE1            => 'Nurse I',
            self::NURSE2            => 'Nurse II',
            self::NURSE3            => 'Nurse III',
            self::NURSE4            => 'Nurse IV',
            self::NURSE5            => 'Nurse V',
            self::NURSE6            => 'Nurse VI',
            self::NURSE7            => 'Nurse VII',

            self::LIBRARIAN1        => 'School Librarian I',
            self::LIBRARIAN2        => 'School Librarian II',
            self::LIBRARIAN3        => 'School Librarian III',

            self::ADMIN_AIDE1       => 'Admin Aide-I', //'Administrative Aide I',
            self::ADMIN_AIDE2       => 'Admin Aide-II', //'Administrative Aide II',
            self::ADMIN_AIDE3       => 'Admin Aide-III', //'Administrative Aide III',
            self::ADMIN_AIDE4       => 'Admin Aide-IV', //'Administrative Aide IV',
            self::ADMIN_AIDE5       => 'Admin Aide-V', //'Administrative Aide V',
            self::ADMIN_AIDE6       => 'Admin Aide-VI', //'Administrative Aide VI',
            self::ADMIN_AIDE7       => 'Admin Aide-VII', //'Administrative Aide VII',

            self::ADMIN_ASST1       => 'Admin Asst. I',//'Administrative Assistant I',
            self::ADMIN_ASST2       => 'Admin Asst. II',//'Administrative Assistant II',
            self::ADMIN_ASST3       => 'Admin Asst. III',//'Administrative Assistant III',
            self::ADMIN_ASST4       => 'Admin Asst. IV',//'Administrative Assistant IV',
            self::ADMIN_ASST5       => 'Admin Asst. V',//'Administrative Assistant V',
            self::ADMIN_ASST6       => 'Admin Asst. VI',//'Administrative Assistant VI',
            self::ADMIN_ASST7       => 'Admin Asst. VII',//'Administrative Assistant VII',

            self::ADMIN_OFFICER1    => 'ADOF-I',//'Administrative Officer I',
            self::ADMIN_OFFICER2    => 'ADOF-II',//'Administrative Officer II',
            self::ADMIN_OFFICER3    => 'ADOF-III',//'Administrative Officer III',
            self::ADMIN_OFFICER4    => 'ADOF-IV',//'Administrative Officer IV',
            self::ADMIN_OFFICER5    => 'ADOF-V',//'Administrative Officer V',

            self::SECURITY_GUARD    => 'Security Guard',
            self::JANITOR           => 'Janitor',
            self::UTILITY1          => 'Utility_I' 
        ];

        return !$flip ? $ranks : array_flip($ranks);
    }
}