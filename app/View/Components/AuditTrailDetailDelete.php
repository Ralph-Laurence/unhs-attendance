<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class AuditTrailDetailDelete extends Component
{
    public $as;
    public $modalLabel;
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null)
    {
        if (empty($as))
            $this->as = 'auditDetailsModal-' . Str::random(6);
        else
            $this->as = $as;

        $this->modalLabel = $as . 'Label';
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.audit-trail-detail-delete');
    }
}
