<?php

namespace App\Http\Middleware;

use App\Http\Utils\AuthGuardNames;
use App\Http\Utils\PortalRouteNames;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) 
        {
            if (Auth::guard($guard)->check()) 
            {
                switch ($guard)
                {
                    case AuthGuardNames::Employee:
                        error_log('auth emp @RedirectIfAuthenticated.php');
                        //return redirect( route(PortalRouteNames::Employee_Home) );
                        return redirect( route(PortalRouteNames::Employee_Attendance) );
                        break;

                    case AuthGuardNames::Admin:
                    //default:
                        error_log('auth adm @RedirectIfAuthenticated.php');
                        return redirect(RouteServiceProvider::HOME);
                        break;
                }
            }

            // Original
            // if (Auth::guard($guard)->check()) 
            // {
            //     return redirect(RouteServiceProvider::HOME);
            // }
        }

        return $next($request);
    }
}
