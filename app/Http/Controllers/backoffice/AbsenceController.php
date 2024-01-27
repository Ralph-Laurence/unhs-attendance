<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Absence;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shared\Filters;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids();
    }
    
    public function index()
    {
        $routes = [
            'ajax_get_all' => route(RouteNames::Absence['get']),
            'deleteRoute'  => route(RouteNames::Absence['delete'])
        ];

        // Role filters will be used for <select> dropdowns
        $roleFilters = array_values(Employee::RoleToString);

        return view('backoffice.absence.index')
            ->with('routes'             , $routes)
            ->with('roleFilters'        , $roleFilters);
    }

    public function destroy(Request $request) 
    {
        $key = $request->input('rowKey');
        $failMessage = Extensions::encodeFailMessage(Messages::GENERIC_DELETE_FAIL);

        try 
        {
            $hash = $this->hashids->decode($key);
            $rowsDeleted = Attendance::where('id', '=', $hash[0])->delete();

            if ($rowsDeleted > 0)
                return Extensions::encodeSuccessMessage(Messages::GENERIC_DELETE_OK);
            else
                return $failMessage;
        }
        catch (Exception $ex) 
        {
            return $failMessage;
        }
    }

    public function getAbsences(Request $request)
    {     
        $selectRange = $request->input('range');

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!in_array($selectRange, Filters::getDateRangeFilters(), true))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $model = new Absence();

        $transactions = [
            Filters::RANGE_TODAY => $model->getDailyAbsences($request),
            Filters::RANGE_WEEK  => $model->getWeeklyAbsences($request),
            Filters::RANGE_MONTH => $model->getMonthlyAbsences($request)
        ];

        $dataset = $transactions[$selectRange];
        
        return $dataset;
    }
}
