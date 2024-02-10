<?php

namespace App\Rules;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DateRangeCompare implements Rule
{
    protected $startDate;
    protected $endDate;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $fmt = Constants::DateFormat;

        $startDate  = Carbon::createFromFormat($fmt, $this->startDate);
        $endDate    = Carbon::createFromFormat($fmt, $this->endDate);

        // Check if End Date is less than Start Date.
        return $startDate->lt($endDate) || $startDate->eq($endDate);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Messages::END_DATE_LESS_THAN;
    }
}
