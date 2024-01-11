<?php

namespace App\Http\Utils;

use Carbon\Carbon;
use Hashids\Hashids;

class Extensions
{
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }

    public static function getCurrentWeek()
    {
        return Carbon::now()->weekOfYear;
    }

    /**
     * Returns an array containing the dates of the week, 
     * with the first date having the array key "start" and 
     * the last date having the array key "end". The dates 
     * are formatted as "f d Y".
     */
    public static function getWeekDateRange($weekNumber, $year) 
    {
        $startDate = Carbon::now()->setISODate($year, $weekNumber, 1)->startOfDay();
        $endDate = Carbon::now()->setISODate($year, $weekNumber, 7)->endOfDay();
    
        $dates = [];

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->format('F d, Y');
        }
    
        return [
            'start' => $dates[0],
            'end' => $dates[count($dates) - 1],
            'dates' => $dates
        ];
    }

    public static function getMonthDateRange($monthNumber, $year) 
    {
       $startDate = Carbon::createFromDate($year, $monthNumber, 1)->startOfDay();
       $endDate = $startDate->copy()->endOfMonth()->endOfDay();

       $dates = [];

       for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
           $dates[] = $date->format('F d, Y');
       }

       return [
           'start' => $dates[0],
           'end' => $dates[count($dates) - 1],
           'dates' => $dates
       ];
    }
    //
    // Dataset Utilities
    //
    public static function hashRowIds($dataset, $salt = null, $minHashLength = 10)
    {
        // Loop through the dataset and replace each id with its hashed equivalent

        $hashids = null;

        if (!is_null($salt) || !empty($salt))
            $hashids = new Hashids($salt, $minHashLength);
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
        $result = ['code' => Constants::XHR_STAT_OK, 'message' => $message] + $extraRows;
    
        return json_encode($result);
    }

    public static function encodeFailMessage($message, $code = null) : string 
    {
        return json_encode([
            'code'    => !is_null($code) ? $code : Constants::XHR_STAT_FAIL,
            'message' => $message
        ]);
    }

    public static function getQRCode_storagePath($append_filename) 
    {
        return storage_path("app/public/qrcodes/$append_filename");
    }

    public static function getQRCode_assetPath($append_filename)
    {
        return asset("storage/qrcodes/$append_filename");
    }
}