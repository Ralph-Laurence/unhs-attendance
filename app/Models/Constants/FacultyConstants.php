<?php
// Not a Model, but a class that holds constant values
namespace App\Models\Constants;

class FacultyConstants extends EmployeeBase
{
    // TEACHER ROLES ARE PREFIXED WITH '10' TO 
    // DIFFER BETWEEN STAFFS WITH PREFIX '20'
    public const TEACHER1                   = 101;
    public const TEACHER2                   = 102;
    public const TEACHER3                   = 103;

    public const SPED_TEACHER1              = 104;
    public const SPED_TEACHER2              = 105;
    public const SPED_TEACHER3              = 106;
    public const SPED_TEACHER4              = 107;

    public const SP_SCI_TEACHER1            = 108;
    public const SP_SCI_TEACHER2            = 109;
    public const SP_SCI_TEACHER3            = 1010;
    public const SP_SCI_TEACHER4            = 1011;
    public const SP_SCI_TEACHER5            = 1012;

    public const HEAD_TEACHER1              = 1013;
    public const HEAD_TEACHER2              = 1014;
    public const HEAD_TEACHER3              = 1015;
    public const HEAD_TEACHER4              = 1016;
    public const HEAD_TEACHER5              = 1017;
    public const HEAD_TEACHER6              = 1018;

    public const MASTER_TEACHER1            = 1019;
    public const MASTER_TEACHER2            = 1020;

    public const ASST_SCHOOL_PRINCIPAL1     = 1021;
    public const ASST_SCHOOL_PRINCIPAL2     = 1022;

    public const SCHOOL_PRINCIPAL1          = 1023;
    public const SCHOOL_PRINCIPAL2          = 1024;
    public const SCHOOL_PRINCIPAL3          = 1025;
    public const SCHOOL_PRINCIPAL4          = 1026;

    public const SUBSTITUTE_TEACHER         = 1027;

    // DESCRIPTIVE POSITIONS
    public static function getRanks(bool $flip = false) : array
    {
        $ranks = [
            self::TEACHER1 => 'Teacher-I',
            self::TEACHER2 => 'Teacher-II',
            self::TEACHER3 => 'Teacher-III',
        
            self::SPED_TEACHER1   => 'Special Education Teacher-I',
            self::SPED_TEACHER2   => 'Special Education Teacher-II',
            self::SPED_TEACHER3   => 'Special Education Teacher-III',
            self::SPED_TEACHER4   => 'Special Education Teacher-IV',
        
            self::SP_SCI_TEACHER1 => 'Special Science Teacher-I',
            self::SP_SCI_TEACHER2 => 'Special Science Teacher-II',
            self::SP_SCI_TEACHER3 => 'Special Science Teacher-III',
            self::SP_SCI_TEACHER4 => 'Special Science Teacher-IV',
            self::SP_SCI_TEACHER5 => 'Special Science Teacher-V',
        
            self::HEAD_TEACHER1   => 'Head Teacher-I',
            self::HEAD_TEACHER2   => 'Head Teacher-II',
            self::HEAD_TEACHER3   => 'Head Teacher-III',
            self::HEAD_TEACHER4   => 'Head Teacher-IV',
            self::HEAD_TEACHER5   => 'Head Teacher-V',
            self::HEAD_TEACHER6   => 'Head Teacher-VI',
        
            self::MASTER_TEACHER1 => 'Master Teacher-I',
            self::MASTER_TEACHER2 => 'Master Teacher-II',
        
            self::ASST_SCHOOL_PRINCIPAL1 => 'Assistant School Principal-I',
            self::ASST_SCHOOL_PRINCIPAL2 => 'Assistant School Principal-II',
        
            self::SCHOOL_PRINCIPAL1 => 'School Principal-I',
            self::SCHOOL_PRINCIPAL2 => 'School Principal-II',
            self::SCHOOL_PRINCIPAL3 => 'School Principal-III',
            self::SCHOOL_PRINCIPAL4 => 'School Principal-IV',
            self::SUBSTITUTE_TEACHER => 'Substitute Teacher'
        ];

        return !$flip ? $ranks : array_flip($ranks);
    }
}