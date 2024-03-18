<?php

// Traits work like Partial Classes.
// We store all value mapping functions inside this trait
namespace App\Models\Traits;

use App\Models\LeaveRequest;
use Illuminate\Support\Arr;

trait LeaveRequestsAudit
{
    private $transformKeys = [
        LeaveRequest::f_Duration        => 'Duration',
        LeaveRequest::f_EndDate         => 'End Date',
        LeaveRequest::f_StartDate       => 'Start Date',
        LeaveRequest::f_LeaveStatus     => 'Leave Status',
        LeaveRequest::f_LeaveType       => 'Leave Type',
        LeaveRequest::f_Emp_FK_ID       => 'Employee Key'
    ];

    private $statusMappings = [];
    private $typeMappings   = [];

    private function mapStatuses($status)
    {
        if ( array_key_exists($status, $this->statusMappings) )
            return $this->statusMappings[$status];

        return $status;
    }

    private function mapTypes($type)
    {
        if ( array_key_exists($type, $this->typeMappings) )
            return $this->typeMappings[$type];

        return $type;
    }

    private function initValueMappings()
    {
        if (empty($this->statusMappings) && empty($this->typeMappings)) 
        {
            $this->statusMappings = LeaveRequest::getStatuses();
            $this->typeMappings   = LeaveRequest::getTypes();
        }
    }

    private function transformValues(&$data, $key, $oldOrNew)
    {
        if (Arr::has($data, "$oldOrNew.$key")) 
        {
            // Get and remove old pair from the array
            $value = Arr::pull($data, "$oldOrNew.$key");

            switch($key)
            {
                case LeaveRequest::f_LeaveStatus:
                    $value = $this->mapStatuses($value);
                    break;

                case LeaveRequest::f_LeaveType:
                    $value = $this->mapTypes($value);
                    break;
            }
            
            // Add new pair with the old value
            Arr::set($data, "$oldOrNew.{$this->transformKeys[$key]}", $value);
        }
    }

    // $data comes from the transformAudit($data) override function.
    public function beautifyTransforms(&$data)
    {
        error_log('data for map -> ' );
        error_log(print_r($data, true));
        $this->initValueMappings();

        foreach($this->transformKeys as $k => $v)
        {
            $this->transformValues($data, $k, 'old_values');
            $this->transformValues($data, $k, 'new_values');
        }
    }
}