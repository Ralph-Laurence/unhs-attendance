<?php

namespace App\Models\Constants;

use App\Models\Faculty;
use App\Models\LeaveRequest;
use App\Models\Staff;

class AuditableTypesProvider
{
    private $auditableTypesOptions = [];
    private $auditableTypesMasked  = [];

    public function registerTypes()
    {
        $this->auditableTypesMasked = [
            'f' => 'App\Models\Faculty',
            's' => 'App\Models\Staff',
            'l' => 'App\Models\LeaveRequest', 
        ];

        $this->auditableTypesOptions = [
            Faculty::getFriendlyName()          => 'f',
            Staff::getFriendlyName()            => 's',
            LeaveRequest::getFriendlyName()     => 'l',
        ];
    }

    public function getAuditableTypes()
    {
        if (empty($this->auditableTypesMasked))
            $this->registerTypes();

        return $this->auditableTypesMasked;
    }

    public function getAuditableTypesOptions()
    {
        if (empty($this->auditableTypesOptions))
            $this->registerTypes();

        return $this->auditableTypesOptions;
    }
}