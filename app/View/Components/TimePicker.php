<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class TimePicker extends Component
{
    public $as;
    public $default;
    public $format;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $default = null, $format = null)
    {
        $this->as       = !is_null($as)      ? $as      : 'time-picker-' . Str::random(6);
        $this->default  = !is_null($default) ? $default : date('h:i a');
        $this->format   = !is_null($format)  ? $format  : $this->getDefaultFormat();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.time-picker', 
        [
            'defaultFormat' => $this->getDefaultFormat()
        ]);
    }

    public function getDefaultFormat()
    {
        return 'HH:MM tt';
    }
}
