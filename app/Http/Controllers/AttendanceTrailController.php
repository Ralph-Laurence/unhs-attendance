<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AttendanceTrailController extends Controller
{
    private $emp_hashids;
    private $fullForm;
    private $shortForm;

    private const TRAIL_RANGE = [
        'today' => 1,
        'week'  => 2,
        'month' => 3,
        'all'   => 4
    ];

    public function __construct() 
    {
        $this->emp_hashids = new Hashids(Employee::HASH_SALT, Employee::MIN_HASH_LENGTH);

        $this->fullForm  = ['Hr', 'Mins', 'Secs'];
        $this->shortForm = ['h', 'm', 's'];
    }

    public function index(Request $request)
    {
        if (empty($request->input('employee-key')))
            return back();

        $key = $request->input('employee-key');
        $id = $this->emp_hashids->decode($key);

        $empDetails = Employee::where('id', '=', $id[0])
            ->select([
                Employee::f_FirstName   . ' as fname',
                Employee::f_MiddleName  . ' as mname',
                Employee::f_LastName    . ' as lname',
                Employee::f_EmpNo       . ' as idNo'
            ])
            ->first();

        if (!$empDetails)
            return back();

        $routes = [
            'trails_all'        => route(RouteNames::Trails['all']),
            'export_trail_pdf'  => route(RouteNames::Trails['exportPdf'])
        ];

        $employee = $empDetails->toArray();

        return view('backoffice.teachers.trails')
            ->with('routes',        $routes)
            ->with('empKey',        $request->input('employee-key'))
            ->with('empName',       implode(' ', [ $employee['fname'], $employee['mname'], $employee['lname'], ]))
            ->with('empIdNo',       $employee['idNo'])
            ->with('trailRange',    self::TRAIL_RANGE);
    }

    public function getTrails(Request $request)
    {
        try
        {
            $key = $request->input('employee-key');
            $id = $this->emp_hashids->decode($key);

            $dataset = $this->readTrailDataset($id[0])['dataset'];
            
            $this->beautifyTimePeriod($dataset);
            
            return json_encode([
                'data' => $dataset,
                //'icon' => Attendance::getIconClasses()
            ]);
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage());
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
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
     * Filter a dataset of attendance trails
     * then return the resulting date range string
     */
    private function filterTrailRange($range, &$query) : string
    {
        $selectTrailRange = [
            //
            // Attendances Today
            //
            self::TRAIL_RANGE['today'] => function(&$query)
            {
                $date = Carbon::now();

                $query->whereBetween('created_at', [
                    $date->startOfDay()->format(Constants::TimestampFormat), 
                    $date->endOfDay()->format(Constants::TimestampFormat)
                ]);

                return date('F d, Y');
            },
            //
            // Attendances This Week
            //
            self::TRAIL_RANGE['week' ] => function(&$query)
            {
                $currentWeek = Extensions::getCurrentWeek();
                $query->where(Attendance::f_WeekNo, '=', $currentWeek);
                
                $weekRange = Extensions::getWeekDateRange($currentWeek, date('Y'));

                return $weekRange['start'] ." - ". $weekRange['end'];
            },
            //
            // Attendances This Month
            //
            self::TRAIL_RANGE['month'] => function(&$query)
            {
                $date = Carbon::now();

                $query->whereBetween('created_at', [
                    $date->startOfMonth()->format(Constants::TimestampFormat), 
                    $date->endOfMonth()->format(Constants::TimestampFormat)
                ]);

                $monthRange = Extensions::getMonthDateRange($date->month, $date->year);

                return $monthRange['start'] ." - ". $monthRange['end'];
            },
            //
            // Attendances This Month
            //
            self::TRAIL_RANGE['all'] => function()
            {
                return "All Records";
            }
        ];

        if (isset($selectTrailRange[$range])) 
        {
            $dateRange = $selectTrailRange[$range]($query);
            return $dateRange;
        }

        return '';
    }

    private function readTrailDataset($employeeId, $range = self::TRAIL_RANGE['all'])
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

        $generated_date_range = $this->filterTrailRange($range, $query);

        $dataset = $query->get()->toArray();

        return [
            'dataset'   => $dataset,
            'dateRange' => $generated_date_range
        ];
    }

    public function exportTrailsReport(Request $request)
    {
        $trailsLayout = 'reports.employee-trail';
        // 'filename' => $request->input('filename'),
        
        $trailRange = $request->input('trail-range');

        if (empty($trailRange) || !$request->filled('trail-range'))
            $trailRange = self::TRAIL_RANGE['all'];

        try
        {
            $key = $request->input('employee-key');
            $id  = $this->emp_hashids->decode($key);

            $employee = DB::table(Employee::getTableName())
                ->where('id', '=', $id[0])
                ->select([
                    Employee::f_EmpNo      . ' as idNo',
                    Employee::f_FirstName  . ' as fname',
                    Employee::f_MiddleName . ' as mname',
                    Employee::f_LastName   . ' as lname',
                ])
                ->first();

            if (!$employee)
                return null;

            $dataset    = $this->readTrailDataset($id[0], $trailRange);
            $trailData  = $dataset['dataset'];
            $dateRange  = $dataset['dateRange'];

            $this->beautifyTimePeriod($trailData);

            $pdf = Pdf::loadView($trailsLayout, [
                'trailData'     => $trailData,
                'stat_absent'   => Attendance::STATUS_ABSENT,
                'unicode_x'     => json_decode('"\\u00d7"'),

                'emp_name'      => implode(' ', [ $employee->fname, $employee->mname, $employee->lname ]),
                'emp_id'        => $employee->idNo,
                'dateRange'     => $dateRange,

                'pdf_banner_org_name'  => Constants::OrganizationName,
                'pdf_banner_org_addr'  => Constants::OrganizationAddress,

            ]);
            
            $pdf->setOption(['isPhpEnabled' => true]);

            // error_log(print_r($trailData, true));
            // build a filename for the PDF
            $out_filename = 'generated_'  . Str::random(6) . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->save(public_path('pdf') . "/$out_filename");
            $pdf = public_path("pdf/$out_filename");

            $blob = response()->download($pdf);
            $blob->deleteFileAfterSend(true);
            
            return $blob;
        }
        catch(Exception $ex)
        {
            // error_log($ex->getMessage());
            return Extensions::encodeFailMessage('Failed to generate report.');
        }
    }

    /**
     * Fix the time duration formatting. 
     * If an hour exists, remove its seconds.
     * This applies to the duration, late, over and undertime
     */
    function formatDuration($duration)
    {
        $duration = str_replace($this->fullForm, $this->shortForm, $duration);

        if (strpos($duration, 'h') !== false) {
            $parts = explode(' ', $duration);
            $duration = $parts[0] . ' ' . $parts[1];
        }
        return $duration;
    }
}

// https://www.youtube.com/watch?v=zb-UuuRK974