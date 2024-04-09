<?php

namespace App\Models;

use App\Models\Base\IModelCommons;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecurityGuard extends Employee implements IModelCommons
{
    use HasFactory;

    // This is the friendly name that will be used when 
    // presenting this model in the Audits table.
    public static function getFriendlyName() : string 
    {
        return 'Guard';
    }

    public static function getBaseName(): string
    {
        $fullClassName = static::class;

        return class_basename($fullClassName);
    }
}