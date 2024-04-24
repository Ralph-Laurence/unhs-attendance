<?php

namespace App\Http\Middleware;

use App\Http\Utils\AuthGuardNames;
use App\Http\Utils\PortalRouteNames;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // if (! $request->expectsJson()) {
        //     return route('login');
        // }
        $routePrefix = $request->route()->getPrefix();

        if (!$request->expectsJson()) 
        {
            if (Str::contains(PortalRouteNames::Employee_Route_Prefix, $routePrefix))
            {
                error_log('Redirect Employee Login');
                return route( PortalRouteNames::Employee_Login );
            }

            error_log('Redirect Admin Login, Authenticate.php');
            return route('login');
        }
    }
}
