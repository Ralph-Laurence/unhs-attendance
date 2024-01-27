<?php

namespace App\Models\Shared;

class Filters
{
    public const RANGE_TODAY = 'this_day';
    public const RANGE_WEEK  = 'this_week';
    public const RANGE_MONTH = 'by_month';

    public static function getDateRangeFilters()
    {
        return [
            'today' => Filters::RANGE_TODAY,
            'week'  => Filters::RANGE_WEEK,
            'month' => Filters::RANGE_MONTH
        ];
    }
}