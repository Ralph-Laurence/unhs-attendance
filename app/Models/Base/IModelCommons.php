<?php

namespace App\Models\Base;

interface IModelCommons
{
    public static function getFriendlyName()    : string;
    public static function getBaseName()        : string;
    // public static function getAuditFieldsMap()  : array;
}
