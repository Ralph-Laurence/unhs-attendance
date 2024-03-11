<?php

namespace App\Http\Utils;

class RegexPatterns
{
    public const ALPHA_DASH_DOT_SPACE   = '/^[A-Za-z\.\- ]+$/';
    public const ALPHA_DASH_DOT         = '/^[A-Za-z\.\-]+$/';
    public const NUMERIC_DASH           = '/^[0-9\-]+$/';
    public const NUMERIC                = '/^[0-9]+$/';
    //public const MOBILE_NO              = '/^[0-9]{11,12}$/';

    // Phone number must begin with "09" followed by next 9 numbers [total 11digits]
    public const MOBILE_NO              = '/^09[0-9]{9}$/';
}