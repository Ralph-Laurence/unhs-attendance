<?php

namespace App\Http\Utils;

class ValidationMessages
{
    public static function required($fieldName = '') : string
    {
        if (!empty($fieldName))
            return "$fieldName must be filled out.";

        return 'Please fill out this field.';
    }

    public static function unique($fieldName) : string
    {
        return "$fieldName has already been taken.";
    }

    public static function minLength($length, $fieldName = '') : string
    {
        if (!empty($fieldName))
            return "$fieldName must be atleast $length characters.";

        return "Must be atleast $length characters.";
    }

    public static function maxLength($length, $fieldName = '') : string
    {
        if (!empty($fieldName))
            return "$fieldName must not exceed $length characters.";

        return "Must not exceed $length characters.";
    }

    public static function invalid($fieldName = '') : string
    {
        if (!empty($fieldName))
            return "$fieldName doesn't seem to be correct.";

        return "Invalid entry. Please try again.";
    }

    public static function numericDash($fieldName = '') : string 
    {
        if (!empty($fieldName))
            return "$fieldName may only contain numbers and dashes.";

        return 'This field may only contain numbers and dashes.';
    }

    public static function alphaDashDot($fieldName = '') : string 
    {
        if (!empty($fieldName))
            return "$fieldName may only contain letters, dots and dashes.";

        return 'This field may only contain letters, dots and dashes.';
    }

    public static function alphaDashDotSpace($fieldName = '') : string 
    {
        if (!empty($fieldName))
            return "$fieldName may only contain letters, space, dots and dashes.";

        return 'This field may only contain letters, space, dots and dashes.';
    }
    
    public static function alphaNumUnderscore($fieldName = '') : string 
    {
        if (!empty($fieldName))
            return "$fieldName may only contain letters, numbers, and underscores.";

        return 'This field may only contain letters, numbers, and underscores.';
    }

    public static function mobile($fieldName = '') : string
    {
        if (!empty($fieldName))
            return "$fieldName must be 11 or 12 digits without special charactes like '#' and '+'.";

        return "Must be 11 or 12 digits without special charactes like '#' and '+'.";
    }

    public static function option($option = null) : string
    {
        $default = 'Please select an option.';

        if (empty($option))
            return $default;

        $ctype = Extensions::getCTypeAlpha($option);

        if ($ctype == Constants::CTYPE_VOWEL)
            return "Please select an $option";

        if ($ctype == Constants::CTYPE_CONSONANT)
            return "Please select a $option";
        
        return $default;
    }

    public static function between($fieldName, $min, $max)
    {
        if (!empty($fieldName))
            return "$fieldName must be between $min and $max.";

        return "The value must be between $min and $max.";
    }

    public static function permission() 
    {
        return 'Please assign the appropriate permissions.';
    }
}