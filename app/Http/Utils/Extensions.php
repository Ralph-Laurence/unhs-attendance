<?php

namespace App\Http\Utils;

class Extensions
{
    public static function prefixArray($prefix, $array) : array
    {
        return preg_filter('/^/', $prefix, $array);
    }
}