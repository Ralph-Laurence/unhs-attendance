<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Shared\Filters;
use Hashids\Hashids;
use Illuminate\Http\Request;

class LeaveRequestsController extends Controller
{
    private $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids();
    }
    
    public function index()
    {
        $routes = [
            'ajax_get_all'      => route(RouteNames::Leave['get']),
            'ajax_load_empids'  => route(RouteNames::AJAX['list-empno']),
            'deleteRoute'       => route(RouteNames::Leave['delete'])
        ];

        // Role filters will be used for <select> dropdowns
        $roleFilters = array_values(Employee::RoleToString);

        return view('backoffice.leave.index')
            ->with('routes'             , $routes)
            ->with('roleFilters'        , $roleFilters);
    }

    public function destroy(Request $request)
    {

    }

    public function getRecords(Request $request)
    {
        $selectRange = $request->input('range');

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!in_array($selectRange, Filters::getDateRangeFilters(), true))
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);

        $model = new LeaveRequest();

        $transactions = [
            Filters::RANGE_TODAY => $model->getDailyLates($request),
            Filters::RANGE_WEEK  => $model->getWeeklyLates($request),
            Filters::RANGE_MONTH => $model->getMonthlyLates($request)
        ];

        $dataset = $transactions[$selectRange];
        
        return $dataset;
    }
}
