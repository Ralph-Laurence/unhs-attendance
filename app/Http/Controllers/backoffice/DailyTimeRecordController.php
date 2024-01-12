<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyTimeRecordController extends Controller
{
    private $emp_hashids;
    private $fullForm;
    private $shortForm;

    private const PERIOD_15TH_MONTH = 'fifteenth';
    private const PERIOD_END_MONTH  = 'end_of_month';
    private const PERIOD_CURRENT    = 'current_month';

    private const PAYROLL_PERIODS   = [
        '15th of Month' => self::PERIOD_15TH_MONTH,
        'End of Month'  => self::PERIOD_END_MONTH,
        'Current Month' => self::PERIOD_CURRENT   
    ];

    public function __construct() 
    {
        $this->emp_hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);

        $this->fullForm  = ['Hr', 'Mins', 'Secs'];
        $this->shortForm = ['h', 'm', 's'];
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
            ->with('dtrPeriods', self::PAYROLL_PERIODS);
    }

    public function getTimeRecords(Request $request)
    {
        $fail  = Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        $empId = $this->decodeEmpKey($request);

        if (empty($empId))
            return $fail;

        // Set a default value to select period if not provided
        $selectRange = $request->input('range', self::PERIOD_CURRENT);

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!in_array($selectRange, self::PAYROLL_PERIODS, true))
            $selectRange = self::PERIOD_CURRENT;

        try
        {
            
            $raw = $this->findTimeRecords($empId, $selectRange);
            $dataset = $raw['dataset'];

            $this->beautifyTimePeriod($dataset);

            return json_encode([
                'data'      => $dataset,
                'daysRange' => $raw['range_days']
            ]);
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage() . " @ " . $ex->getLine());
            return $fail;
        }
    }

    private function findTimeRecords($empId, $range)// = self::PERIOD_CURRENT)
    {
        $actions = [

            //
            // Get attendances of Current month
            //
            self::PERIOD_CURRENT => function($empId)
            {
                $date = Carbon::now();

                $dataset = $this->buildSelectQuery($empId)
                    ->whereBetween('created_at', [
                        $date->startOfMonth()->format(Constants::TimestampFormat), 
                        $date->endOfMonth()->format(Constants::TimestampFormat)
                    ])
                    ->get()->toArray();

                $monthRange = Extensions::getMonthDateRange($date->month, $date->year);

                return [
                    'range_days' => $monthRange['start'] ." - ". $monthRange['end'],
                    'dataset'    => $dataset
                ];
            },
            //
            // Get attendances in First-Half weeks of month
            //
            self::PERIOD_15TH_MONTH => function($empId) 
            {
                $from = Carbon::now()->startOfMonth();
                $to   = Carbon::now()->startOfMonth()->addDays(14);

                $dataset = $this->buildSelectQuery($empId)
                    ->whereBetween('created_at', [$from, $to])
                    ->get()
                    ->toArray();

                $daysRange = Extensions::getPeriods($from, $to);

                return [
                    'range_days' => $daysRange,
                    'dataset'    => $dataset
                ];
            },
            //
            // Get attendances in Second-Half weeks of month
            //
            self::PERIOD_END_MONTH => function($empId)
            {
                
                $from = Carbon::now()->startOfMonth()->addDays(15);
                $to = Carbon::now()->endOfMonth();

                $dataset = $this->buildSelectQuery($empId)
                    ->whereBetween('created_at', [$from, $to])
                    ->get()->toArray();

                $daysRange = Extensions::getPeriods($from, $to);

                return [
                    'range_days' => $daysRange,
                    'dataset'    => $dataset
                ]; 
            },
        ];

        if (isset($actions[$range])) 
            return $actions[$range]($empId);

        return [];
    }

    public function exportPdf(Request $request)
    {
        error_log(print_r($request->all(), true));

        $key = 0; 
        $empId  = $this->decodeEmpKey($request, $key);

         if (empty($empId))
            return null;

        $employee = $this->getEmployeeDetails($empId);

        if (!$employee)
            return null;

        $trailsLayout = 'reports.employee-trail';

        // Set a default value to select period if not provided
        $selectRange = $request->input('range', self::PERIOD_CURRENT);

        // Make sure that the select range is one of the allowed values.
        // If not, set its default select period
        if (!in_array($selectRange, self::PAYROLL_PERIODS, true))
            $selectRange = self::PERIOD_CURRENT;

        try
        {
            $raw = $this->findTimeRecords($empId, $selectRange);
            $dataset = $raw['dataset'];

            $this->beautifyTimePeriod($dataset);

            $pdf = Pdf::loadView($trailsLayout, [
                'dataSet'       => $dataset,
                'stat_absent'   => Attendance::STATUS_ABSENT,
                'unicode_x'     => json_decode('"\\u00d7"'),

                'emp_name'      => implode(' ', [ $employee->fname, $employee->mname, $employee->lname ]),
                'emp_id'        => $employee->idNo,
                'dateRange'     => $raw['range_days'],

                'pdf_banner_org_name'  => Constants::OrganizationName,
                'pdf_banner_org_addr'  => Constants::OrganizationAddress,

            ]);
            
            $pdf->setOption(['isPhpEnabled' => true]);

            // error_log(print_r($trailData, true));
            // build a filename for the PDF
            $range_as_fileName = array_flip(self::PAYROLL_PERIODS)[$selectRange];

            $out_filename = "$range_as_fileName-" . date('Y-m-d_H-i-s') . '.pdf';
            $temp_filename = "temp-$out_filename";
            // $pdf->save(public_path('pdf') . "/$out_filename");
            // $pdf = public_path("pdf/$out_filename");

            // $blob = response()->download($pdf);
            // $blob->deleteFileAfterSend(true);
            
            // return json_encode([
            //     'blob' => $blob
            // ]);
            
            $pdf->save(public_path('pdf') . "/$temp_filename");
            $pdfPath = public_path("pdf/$temp_filename");
            
            $fileData = file_get_contents($pdfPath);
            $base64FileData = base64_encode($fileData);
            
            // Delete the temporary file
            unlink($pdfPath);

            return json_encode([
                'fileData' => $base64FileData,
                'filename' => $out_filename
            ]);
        }
        catch(Exception $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage('Failed to generate report.');
        }
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

    private function buildSelectQuery($employeeId) : Builder
    {
        $query = DB::table(Attendance::getTableName())
            ->where(Attendance::f_Emp_FK_ID, '=', $employeeId)
            ->select([
                DB::raw('DATE_FORMAT('   . Attendance::f_TimeIn     . ', "%l:%i %p") as am_in'),
                DB::raw('DATE_FORMAT('   . Attendance::f_LunchStart . ', "%l:%i %p") as am_out'),
                DB::raw('DATE_FORMAT('   . Attendance::f_LunchEnd   . ', "%l:%i %p") as pm_in'),
                DB::raw('DATE_FORMAT('   . Attendance::f_TimeOut    . ', "%l:%i %p") as pm_out'),
                Attendance::f_Duration   . ' as duration',
                Attendance::f_Late       . ' as late',
                Attendance::f_UnderTime  . ' as undertime',
                Attendance::f_OverTime   . ' as overtime',
                Attendance::f_Status     . ' as status',
                'created_at',
                DB::raw('DAY(created_at) as day_number'),
                DB::raw('DATE_FORMAT(created_at, "%a") as day_name')
            ]);

        return $query;
    }

    
    private function beautifyTimePeriod($dataset)
    {
        // Convert the time duration format.
        // Replace the full form into shorter form
        foreach ($dataset as &$data) 
        {
            $data->duration  = $this->formatDuration($data->duration);
            $data->late      = $this->formatDuration($data->late);
            $data->undertime = $this->formatDuration($data->undertime);
            $data->overtime  = $this->formatDuration($data->overtime);
        }

        unset($data); // unset reference to last element
    }

     /**
     * Fix the time duration formatting. 
     * If an hour exists, remove its seconds.
     * This applies to the duration, late, over and undertime
     */
    private function formatDuration($duration)
    {
        $duration = str_replace($this->fullForm, $this->shortForm, $duration);

        if (strpos($duration, 'h') !== false) {
            $parts = explode(' ', $duration);
            $duration = $parts[0] . ' ' . $parts[1];
        }
        return $duration;
    }
}

/**
 * $firstHalf = Attendance::whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->startOfMonth()->addDays(14)])->get();
$secondHalf = Attendance::whereBetween('date', [Carbon::now()->startOfMonth()->addDays(15), Carbon::now()->endOfMonth()])->get();

return view('your_view', ['firstHalf' => $firstHalf, 'secondHalf' => $secondHalf]);
 */