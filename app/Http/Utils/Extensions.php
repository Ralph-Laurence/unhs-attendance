<?php

namespace App\Http\Utils;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Hashids\Hashids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Extensions
{
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }

    /**
     * Gets the week number (1 - 52) of current year.
     */
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

    public static function getPeriods($dateFrom, $dateTo, $format = 'F d, Y') : string
    {
        $period = new CarbonPeriod($dateFrom, $dateTo);

        $dates = [];

        foreach($period as $date) {
            $dates[] = $date->format($format);
        }

        return $dates[0] .' - '. $dates[count($dates) -1 ];
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
        // Include optional extra rows
        $result = ['code' => Constants::XHR_STAT_OK, 'message' => $message] + $extraRows;
    
        return json_encode($result);
    }

    public static function encodeFailMessage($message, $code = null, $extraRows = []) : string 
    {
        // Include optional extra rows
        $statCode = !is_null($code) ? $code : Constants::XHR_STAT_FAIL;

        $result = ['code' => $statCode, 'message' => $message] + $extraRows;
    
        return json_encode($result);

        // return json_encode([
        //     'code'    => !is_null($code) ? $code : Constants::XHR_STAT_FAIL,
        //     'message' => $message
        // ]);
    }

    public static function validationFailResponse($validator, $extraRows = []) : array
    {
        $errors = $validator->errors();

        if ($extraRows)
        {
            foreach ($extraRows as $field => $message) {
                $errors->add($field, $message);
            }
        }
        return [
            'code'   => Constants::ValidationStat_Failed,
            'errors' => $errors
        ];
    }
    
    public static function validationSuccessResponse($validator) : array
    {
        return [
            'code' => Constants::ValidationStat_Success,
            'data' => $validator
        ];
    }

    //'code' => Constants::ValidationStat_Failed

    public static function getQRCode_storagePath($append_filename) 
    {
        return storage_path("app/public/qrcodes/$append_filename");
    }

    public static function getQRCode_assetPath($append_filename)
    {
        return asset("storage/qrcodes/$append_filename");
    }

    /**
     * "range(1, 12)" generates an array of numbers from 1 to 12.
     * "collect()" turns this array into a Laravel collection.
     * "mapWithKeys" transforms each element of the collection into 
     *               an associative array where the key is the month 
     *               name and the value is the month index.
     * "DateTime::createFromFormat('!m', $index)->format('F')" 
     *              converts the month index into a full month 
     *              name, such as January through December.
     * "$months->all()" returns the resulting associative array.
     */
    public static function getMonthsAssoc()
    {
        $months = collect(range(1, 12))->mapWithKeys(function ($index) {
            return [DateTime::createFromFormat('!m', $index)->format('F') => $index];
        });
        
        return $months->all();
    }

    public static function mapCaseWhen($haystack = array(), $needle = '', $as = '') : string
    {
        $mapping = "CASE ";
        
        foreach ($haystack as $key => $value) {
            $mapping .= "WHEN $needle = $key THEN '$value' ";
        }

        $mapping .= "END as $as";

        return $mapping;
    }
}