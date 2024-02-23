<?php

namespace App\Models;

use App\Http\Utils\Extensions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyTimeRecord extends Model
{
    use HasFactory;

    public const PERIOD_15TH_MONTH = 'fifteenth';
    public const PERIOD_END_MONTH  = 'end_of_month';
    public const PERIOD_CURRENT    = 'current_month';

    public const PAYROLL_PERIODS   = [
        '15th of Month' => self::PERIOD_15TH_MONTH,
        'End of Month'  => self::PERIOD_END_MONTH,
        'Current Month' => self::PERIOD_CURRENT   
    ];

    public function fromFirstHalfMonth($empId)
    {
        $from = Carbon::now()->startOfMonth();
        $to   = Carbon::now()->startOfMonth()->addDays(14);

        $dataset   = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();
        $daysRange = Extensions::getPeriods($from, $to);

        return [
            'range_days' => $daysRange,
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->sumDtrTimes($dataset)
        ];
    }

    public function fromCurrentMonth($empId)
    {
        $from = Carbon::now()->startOfMonth();
        $to   = Carbon::now()->endOfMonth();

        $dataset = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();

        $date = Carbon::now();
        $monthRange = Extensions::getMonthDateRange($date->month, $date->year);

        return [
            'range_days' => $monthRange['start'] ." - ". $monthRange['end'],
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->sumDtrTimes($dataset)
        ];
    }

    public function fromEndOfMonth($empId)
    {
        $from = Carbon::now()->startOfMonth()->addDays(15);
        $to   = Carbon::now()->endOfMonth();

        $dataset = $this->buildSelectQuery($empId, $from, $to)->get()->toArray();

        $daysRange = Extensions::getPeriods($from, $to);

        return [
            'range_days' => $daysRange,
            'dataset'    => $dataset,
            'weekends'   => Extensions::getWeekendNumbersInRange($from, $to),
            'statistics' => $this->sumDtrTimes($dataset)
        ]; 
    }

    private function buildSelectQuery(int $employeeId, Carbon $from, Carbon $to) // : Builder
    {
        $seriesAlias    = 'dateseries';

        // Set the default status as Absent when null
        $col_status     = Attendance::f_Status;
        $statusCoalesce = Attendance::STATUS_ABSENT;

        $query = DB::table(Extensions::getDateSeriesRaw($from, $to, $seriesAlias))
            ->leftJoin(Attendance::getTableName(), function($join) use($seriesAlias, $employeeId)
            {
                $join->on(DB::raw("DATE($seriesAlias.date)"), '=', DB::raw('DATE(created_at)'));
                $join->on(Attendance::f_Emp_FK_ID,            '=', DB::raw("$employeeId"));
            })
            ->select([
                DB::raw("EXTRACT(DAY FROM $seriesAlias.date)      AS day_number"),
                DB::raw("DATE_FORMAT($seriesAlias.date, '%a')     AS day_name"),
                //DB::raw("COALESCE($col_status, '$statusCoalesce') AS status"),
                DB::raw("CASE
                    WHEN DATE_FORMAT($seriesAlias.date, '%w') IN (0, 6) THEN 'Rest'
                    ELSE COALESCE($col_status, '$statusCoalesce')
                END AS status"),

                $this->toShortTimeRaw(Attendance::f_TimeIn,     'am_in'),
                $this->toShortTimeRaw(Attendance::f_LunchStart, 'am_out' ),
                $this->toShortTimeRaw(Attendance::f_LunchEnd,   'pm_in'),
                $this->toShortTimeRaw(Attendance::f_TimeOut,    'pm_out'),

                Attendance::f_Duration . ' as duration_raw',    // unformatted duration string

                Attendance::timeStringToDurationRaw(Attendance::f_Duration , null),
                Attendance::timeStringToDurationRaw(Attendance::f_Late     , null, 'late'),
                Attendance::timeStringToDurationRaw(Attendance::f_UnderTime, null, 'undertime'),
                Attendance::timeStringToDurationRaw(Attendance::f_OverTime , null, 'overtime'),
                'created_at',
            ]);
        //error_log($query->toSql());
        return $query;
    }

    private function toShortTimeRaw($time, $as = 'time')
    {
        $format = "%l:%i %p";
        $sql = "DATE_FORMAT($time, '$format') as $as";

        return DB::raw($sql);
    }

    private function sumDtrTimes(array $dataset) : array//, array $times) 
    {
        $totalWorkHrs = Carbon::createFromTime(0, 0, 0);
    
        foreach($dataset as $data)
        {
            if (empty($data->duration_raw))
                continue;
            
            // Create a Carbon instance from the time string
            $workHrs = Carbon::createFromFormat('H:i:s', $data->duration_raw);

            $totalWorkHrs->addHours($workHrs->hour)
                         ->addMinutes($workHrs->minute)
                         ->addSeconds($workHrs->second);
        }

        $str_total_work_hrs = '';

        if ($totalWorkHrs->hour > 0) {
            $str_total_work_hrs = $totalWorkHrs->format('G\\h i\\m');  // Outputs like '4h 31m'
        } else {
            $str_total_work_hrs = $totalWorkHrs->format('i\\m s\\s');  // Outputs like '31m 24s'
        }

        return [
            'totalWorkHrs' => $str_total_work_hrs
        ];

        // foreach ($times as $time) 
        // {
        //     // Create a Carbon instance from the time string
        //     $timeInstance = Carbon::createFromFormat('H:i:s', $time);
            
        //     // Add the time to the total duration
        //     $total->addHours($timeInstance->hour)
        //                   ->addMinutes($timeInstance->minute)
        //                   ->addSeconds($timeInstance->second);
        // }
    
        // // Format the total duration as a time string and return it
        // return $total->format('H:i:s');
    }
}
