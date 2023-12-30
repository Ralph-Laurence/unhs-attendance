<?php

namespace App\Http\Utils;

class Extensions
{
    private const XHR_STAT_OK    = 0;
    private const XHR_STAT_FAIL  = -1;
    
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }

    public static function encodeSuccessMessage($message, $extraRows = []) : string 
    {
        // Use the array union operator (+) to merge the arrays
        $result = ['code' => self::XHR_STAT_OK, 'message' => $message] + $extraRows;
    
        return json_encode($result);
    }

    public static function encodeFailMessage($message) : string 
    {
        return json_encode([
            'code'    => self::XHR_STAT_FAIL,
            'message' => $message
        ]);
    }
}