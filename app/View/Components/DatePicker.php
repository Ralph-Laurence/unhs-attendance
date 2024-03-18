<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class DatePicker extends Component
{
    public $as;
    public $default;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $default = null)
    {
        $this->as       = !is_null($as)      ? $as      : 'date-picker-' . Str::random(6);
        $this->default  = !is_null($default) ? $default : date('F d, Y');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.date-picker');
    }
}
