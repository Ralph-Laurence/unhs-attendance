<?php

namespace App\Http\Utils;

use Carbon\Carbon;
use Hashids\Hashids;

class Extensions
{
    private const XHR_STAT_OK    = 0;
    private const XHR_STAT_FAIL  = -1;
    
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }

    public static function getCurrentWeek()
    {
        return Carbon::now()->weekOfYear;
    }

    //
    // Dataset Utilities
    //
    public static function hashRowIds($dataset, $salt = null)
    {
        // Loop through the dataset and replace each id with its hashed equivalent

        $hashids = null;

        if (!is_null($salt) || !empty($salt))
            $hashids = new Hashids($salt, 10);
        else
            $hashids = new Hashids();
        
        foreach ($dataset as $data) {
            $data->id = $hashids->encode($data->id);
        }
    }
    
    //
    //  JSON RESPONSE BUILDERS
    //
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