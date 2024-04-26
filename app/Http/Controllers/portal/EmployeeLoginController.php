<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use App\Http\Utils\AuthGuardNames;
use App\Http\Utils\PortalRouteNames;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLoginController extends Controller
{
    public function index()
    {
        return view('portal.auth.employee-login')
            ->with('postAction', route( PortalRouteNames::Employee_Auth ));
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'idno' => 'required',
            'pin'  => 'required',
        ]);

        $credentials = $request->only('idno', 'pin');
     
        $user = Employee::where(Employee::f_EmpNo, $credentials[ 'idno' ])->first();

        if ($user && decrypt($user->getAttribute(Employee::f_PINCode)) === $credentials['pin']) 
        {
            Auth::guard( AuthGuardNames::Employee )->login($user);
            //return redirect()->intended( route(PortalRouteNames::Employee_Home) );
            return redirect()->intended( route(PortalRouteNames::Employee_Attendance) );
        }
        else 
        {
            // Authentication failed...
            return back()->withErrors([
                //'message' => 'The provided credentials do not match our records.',
                'idno' => 'Incorrect Id Number or Pin Code. Please try again.'
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::guard( AuthGuardNames::Employee )->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect( route(PortalRouteNames::Employee_Login) );
    }
}
