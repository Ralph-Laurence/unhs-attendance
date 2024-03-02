<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MonthPicker extends Component
{
    public $as;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as)
    {
        $this->as = $as;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.month-picker');
    }
}
