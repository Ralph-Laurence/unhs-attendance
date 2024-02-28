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
            'ajax_export_pdf'   => route(RouteNames::DailyTimeRecord['exportPdf'])
        ];

        return view('backoffice.daily-time-record.index')
            ->with('routes',     $routes)
            ->with('empKey',     $key)
            ->with('empName',    implode(' ', [ $empDetails->lname, ',', $empDetails->fname, $empDetails->mname, ]))
            ->with('empIdNo',    $empDetails->idNo)
            ->with('dtrPeriods', DailyTimeRecord::PAYROLL_PERIODS);
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
        if (!in_array($selectRange, DailyTimeRecord::PAYROLL_PERIODS, true))
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INCOMPLETE);

        try
        {
            $dtr = new DailyTimeRecord;
            $raw = null;

            $filters = [
                // Get attendances of Current month
                DailyTimeRecord::PERIOD_CURRENT => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromCurrentMonth($empId);
                },
                // Get attendances in First-Half weeks of month
                DailyTimeRecord::PERIOD_15TH_MONTH => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromFirstHalfMonth($empId);
                },
                // Get attendances in Second-Half weeks of month
                DailyTimeRecord::PERIOD_END_MONTH => function() use($empId, $dtr, &$raw) {
                    $raw = $dtr->fromEndOfMonth($empId);
                },
            ];
            
            // Check if a key exists in the filter before calling the function
            if (isset($filters[$selectRange])) 
                $filters[$selectRange]();
            else
                throw new ItemNotFoundException('Filter is not found');

            return json_encode([
                'data'          => $raw['dataset'],
                'daysRange'     => $raw['range_days'],
                'weekendDays'   => $raw['weekends'],
                'statistics'    => $raw['statistics']
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

        // Get employee details first to make sure that he exists
        try 
        {
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
        $adapter = $dtr->makePrintableData($empId);

        $pdf = Pdf::loadView('reports.dtr-print', [
            'adapter'    => $adapter,
            'statLeave'  => Employee::ON_STATUS_LEAVE,
            'empDetails' => $empDetails,
            'monthOf'    => date('F Y')
        ]);

        // build a filename for the PDF
        //$range_as_fileName = array_flip(DailyTimeRecord::PAYROLL_PERIODS)[$selectRange];

        $out_filename = $empDetails->empname ."-". date('Y-m-d_H-i-s') . '.pdf';
        $temp_filename = "temp-$out_filename";

        $pdf->save(public_path('pdf') . "/$temp_filename");
        $pdfPath = public_path("pdf/$temp_filename");

        $fileData = file_get_contents($pdfPath);
        $base64FileData = base64_encode($fileData);

        // Delete the temporary file
        unlink($pdfPath);

        return Extensions::encodeSuccessMessage('success', [
            'fileData' => $base64FileData,
            'filename' => $out_filename
        ]);
        // return json_encode([
        //     'fileData' => $base64FileData,
        //     'filename' => $out_filename
        // ]);
    }

    public function printTest($id)
    {
        $dtr = new DailyTimeRecord;
        $adapter = $dtr->printTest($id);

        $statLeave = Employee::ON_STATUS_LEAVE;

        return view('reports.dtr-print')
            ->with('adapter'   , $adapter)
            ->with('statLeave' , $statLeave);
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
                Employee::f_EmpNo       . ' as idNo'
            ])
            ->first();

        return $data;
    }
}