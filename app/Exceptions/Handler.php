<?php

namespace App\Exceptions;

use App\Http\Utils\PortalRouteNames;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use illuminate\Support\Str;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // $routeName = $request->route()->getName();
        // error_log("THE ROUTE ------> $routeName");
        // error_log("ROUTE IS: " . $request->routeIs(PortalRouteNames::Employee_Prefix . '.*') ? 'yeah' : 'nope');

        //if ($request->routeIs(PortalRouteNames::Employee_Prefix . '.*')) 
        $routePrefix = $request->route()->getPrefix();

        if (Str::contains(PortalRouteNames::Employee_Route_Prefix, $routePrefix))
        {
            error_log("Redirect Emp Login @Handler.php");
            return redirect()->guest(route( PortalRouteNames::Employee_Login ));
        }
        
        error_log("Redirect admin login @Handler.php");
        return redirect()->guest(route('login'));
    }
}
