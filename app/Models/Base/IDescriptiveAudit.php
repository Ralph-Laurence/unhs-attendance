<?php

namespace App\Models\Base;

interface IDescriptiveAudit
{
    // This field mapping feature converts table fields to 
    // human-readable equivalent
    public static function getAuditFieldsMap(): array;
}    