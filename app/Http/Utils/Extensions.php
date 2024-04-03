<?php

namespace App\Http\Utils;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use DateTime;
use Hashids\Hashids;
use Illuminate\Support\Facades\DB;

class Extensions
{
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }

    /** 
     * Takes a date range as input and returns an array of day numbers 
     * that fall on a weekend within that range. 
     * This assumes that the date range are in the format of 'Y-m-d'
     * */
    public static function getWeekendNumbersInRange($start, $end)
    {
        $weekendNumbers = [];

        for ($date = $start; $date->lte($end); $date->addDay()) 
        {
            if ($date->isWeekend()) 
                $weekendNumbers[] = $date->day;
        }

        return $weekendNumbers;
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
    
    /** Returns a string that represents the range of dates from $dateFrom to $dateTo. */
    public static function getPeriods($dateFrom, $dateTo, $format = 'F d, Y') : string
    {
        $period = new CarbonPeriod($dateFrom, $dateTo);

        $dates = [];

        foreach($period as $date) {
            $dates[] = $date->format($format);
        }

        return $dates[0] .' - '. $dates[count($dates) -1 ];
    }

    public static function getDateSeriesRaw(Carbon $from, Carbon $to, string $as = 'dates')
    {
        $sql =
        "(
            WITH RECURSIVE dates AS 
            (
                SELECT '$from' AS date
                UNION ALL
                SELECT DATE_ADD(date, INTERVAL 1 DAY)
                FROM dates
                WHERE DATE_ADD(date, INTERVAL 1 DAY) <= '$to'
            )
            SELECT date FROM dates
        ) as $as";

        return DB::raw($sql);
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
        
        foreach ($dataset as $data) 
        {
            if ($data->id)
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
            $mapping .= "WHEN $needle = '$key' THEN '$value' ";
        }

        $mapping .= "END as $as";

        return $mapping;
    }

    /**
     * Formats a sql date into "%b %d, %Y"
     * which means Three letter month, two-digit day and full year.
     * 
     * By default, it formats the created_at field and gives it an alias 'date'
     */
    public static function date_format_bdY($field = 'created_at', $alias = 'date')
    {
        return DB::raw("DATE_FORMAT($field, '%b %d, %Y') as $alias");
    }

    /**
     * Formats a sql date into "%h %i, %p"
     * which means 12-hour time with AM/PM
     * 
     * By default, it formats the created_at field without the seconds 
     * and gives it an alias 'time'
     */
    public static function time_format_hip($field = 'created_at', $alias = 'time', $includeSeconds = false)
    {
        $secs = ":%s";

        if (!$includeSeconds)
            $secs = '';

        return DB::raw("DATE_FORMAT($field, '%h:%i$secs %p') as $alias");
    }

    /**
     * Returns the time duration in HH:MM:SS format.
     * Expects the duration parameter as a float.
     */
    public static function durationToTimeString(float $duration)
    {
        // tell if zero duration |> '00:00:00'
        if (!$duration || $duration == 0)
            return Constants::ZERO_DURATION;
        
        $hours   = floor($duration);
        $minutes = floor(($duration - $hours) * 60);
        $seconds = floor((($duration - $hours) * 60 - $minutes) * 60);
    
        // Pad the values with leading zeros if they are less than 10
        $hours   = str_pad($hours,   2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
    
        // Return the formatted time
        return "{$hours}:{$minutes}:{$seconds}";
    }

    /**
     * Convert the time string back to duration
     */
    public static function timeStringToDuration(string $timeString)
    {
        // Split the time string into hours, minutes, and seconds
        $parts = explode(':', $timeString);

        $hours   = intval($parts[0]);
        $minutes = intval($parts[1]);
        $seconds = intval($parts[2]);

        // Format the duration
        $result = '';
        if ($hours > 0)
            $result .= $hours . 'Hr' . ($hours > 1 ? 's' : '') . ' ';

        if ($minutes > 0)
            $result .= $minutes . 'min' . ($minutes > 1 ? 's' : '') . ' ';

        if ($seconds > 0)
            $result .= $seconds . 'sec' . ($seconds > 1 ? 's' : '');

        // Check if the result is still empty
        if (empty($result)) {
            $result = '0Hrs 0mins 0secs';
        }

        return trim($result);
    }

    /**
     * Checks if a word begins with a vowel or consonant.
     * 1 - Vowel
     * 2 - Consonant
     * 0 - Not a letter
     */
    public static function getCTypeAlpha($word)
    {
        if (empty($word))
            return 0;

        $word   = strtolower($word);
        $vowels = ['a', 'e', 'i', 'o', 'u'];
        $first  = $word[0];

        if (in_array($first, $vowels))
            return Constants::CTYPE_VOWEL;

        if (ctype_alpha($first))
            return Constants::CTYPE_CONSONANT;

        return 0;
    }
}