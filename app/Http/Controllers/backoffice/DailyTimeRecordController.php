<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;

class DailyTimeRecordController extends Controller
{
    private $emp_hashids;

    public function __construct() 
    {
        $this->emp_hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);
    }

    // $filename = $request->input('filename');
    public function index(Request $request)
    {
        $key = 0; 
        $id  = $this->decodeEmpKey($request, $key);

         if (empty($id))
            return back();

        $empDetails = $this->getEmployeeDetails($id);

        if (!$empDetails)
            return back();

        $routes = [
            'ajax_dtr_get_all'  => route(RouteNames::DailyTimeRecord['get']),
            'ajax_export_pdf'   => route(RouteNames::DailyTimeRecord['exportPdf']),
        ];

        return view('backoffice.daily-time-record.index')
            ->with('routes',        $routes)
            ->with('empKey',        $key)
            ->with('empName',       implode(' ', [ $empDetails->lname, ',', $empDetails->fname, $empDetails->mname, ]))
            ->with('empIdNo',       $empDetails->idNo)
            ->with('empRole',       Employee::RoleToString[$empDetails->role])
            ->with('dtrPeriods',    DailyTimeRecord::MONTH_PERIODS)
            ->with('rangeOther',    DailyTimeRecord::PERIOD_OTHER_MONTH)
            
            ->with('defaultRange_Label', DailyTimeRecord::STR_PERIOD_CURRENT)
            ->with('defaultRange_Value', DailyTimeRecord::PERIOD_CURRENT);
    }

    public function getTimeRecords(Request $request)
    {
        $empId = $this->decodeEmpKey($request);

        if (empty($empId))
            throw new ModelNotFoundException('Employee not found');

        // Set a default value to select period if not provided
        $selectRange = $request->input('range', DailyTimeRecord::PERIOD_CURRENT);

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!array_key_exists($selectRange, DailyTimeRecord::MONTH_PERIODS))
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INCOMPLETE);
        
        $otherMonth = $request->input('month', null);

        try
        {
            $dtr = new DailyTimeRecord;
            $raw = null;

            $filters = [
                // Get attendances of Current month
                DailyTimeRecord::PERIOD_CURRENT     => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromCurrentMonth($empId);
                },
                // Get attendances in First-Half weeks of month
                DailyTimeRecord::PERIOD_15TH_MONTH  => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromFirstHalfMonth($empId);
                },
                // Get attendances in Second-Half weeks of month
                DailyTimeRecord::PERIOD_END_MONTH   => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromEndOfMonth($empId);
                },
                // Get attendances of Other Months
                DailyTimeRecord::PERIOD_OTHER_MONTH => function() use($empId, $dtr, &$raw, $otherMonth) 
                {
                    if (is_null($otherMonth))
                        throw new ItemNotFoundException('Filter is not found');

                    $raw = $dtr->fromOtherMonth($empId, $otherMonth);
                },
            ];
            
            // Check if a key exists in the filter before calling the function
            if (isset($filters[$selectRange])) 
                $filters[$selectRange]();
            else
                throw new ItemNotFoundException('Filter is not found');

            return json_encode([
                'data'        => $raw['dataset'],
                'dtrRange'    => $raw['dtr_range'],
                'weekendDays' => $raw['weekends'],
                'statistics'  => $raw['statistics']
            ]);
        }
        catch (ModelNotFoundException $ex) {
            // When no records of dtr or employee were found
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        }
        catch (ItemNotFoundException $ex) {
            // The filter supplied for the date periods is not present or not allowed
            return Extensions::encodeFailMessage(Messages::DTR_PERIOD_UNRECOGNIZED);
        }
        catch (Exception $ex) {
            error_log($ex->getMessage());
            error_log($ex);
            // Handle general error
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    public function exportPdf(Request $request)
    {
        $empId = $this->decodeEmpKey($request, $key);

         if (empty($empId))
            return null;

        $empDetails = null;
        $from       = null;
        $to         = null;
        $monthOf    = null;

        // Get employee details first to make sure that he exists
        try 
        {
            $monthNumber = $request->input('month', null);

            if (!is_null($monthNumber))
            {
                $year = Carbon::now()->year; // Get the current year
                $from = Carbon::create($year, $monthNumber, 1)->startOfMonth(); // Start of the specific month
                $to   = Carbon::create($year, $monthNumber, 1)->endOfMonth();   // End of the specific month
            }
            else
            {
                $from = Carbon::now()->startOfMonth();
                $to   = Carbon::now()->endOfMonth();
            }

            // Format the date to "F Y"
            // We will use this to indicate the range of the printed DTR
            $monthOf = $from->format('F Y');

            $empDetails = Employee::where('id', '=', $empId)
            ->select([
                Employee::getConcatNameDbRaw(''),
                Employee::f_EmpNo . ' as empno'
            ])
            ->firstOrFail();
        } 
        catch (ModelNotFoundException $ex) {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::PROCESS_REQUEST_FAILED);
        }

        // Assume successful retrieving of data
        $dtr = new DailyTimeRecord;
        $adapter = $dtr->makePrintableData($empId, $from, $to);

        $thisMonth = Carbon::now()->format('m');

        // Will be used for indicators
        $selectedDtrPeriod = ($thisMonth == $from->format('m')) 
                           ? DailyTimeRecord::PERIOD_CURRENT 
                           : $from->format('F Y');

        return Extensions::encodeSuccessMessage('success', [
            'dataset'    => $adapter['dataset'],
            'undertime'  => $adapter['undertime'],
            'statLeave'  => Employee::ON_STATUS_LEAVE,
            'empDetails' => $empDetails,
            'monthOf'    => $monthOf,
            'dtrPeriod'  => $selectedDtrPeriod
        ]);
    }

    private function decodeEmpKey(Request $request, &$out_raw_key = null) 
    {
        $emp_key = 'employee-key';

        if (!$request->has($emp_key) || !$request->filled($emp_key))
            return 0;

        try 
        {
            $key = $request->input($emp_key);
            $id  = $this->emp_hashids->decode($key);
    
            $out_raw_key = $key;
    
            return $id[0];
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage() . " @ " . $ex->getLine());
            return 0;
        }
    }

    private function getEmployeeDetails($id)
    {
        $data = DB::table(Employee::getTableName())
            ->where('id', '=', $id)
            ->select([
                Employee::f_FirstName   . ' as fname',
                Employee::f_MiddleName  . ' as mname',
                Employee::f_LastName    . ' as lname',
                Employee::f_Role    . ' as role',
                Employee::f_EmpNo       . ' as idNo'
            ])
            ->first();

        return $data;
    }
}