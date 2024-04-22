<?php 

namespace App\Http\Utils;

class AuthGuardNames
{
    public const Employee = 'employee';
    // the default guard that fortify uses. 
    // This is not directly used by fortify.
    public const Admin    = 'web'; 
}