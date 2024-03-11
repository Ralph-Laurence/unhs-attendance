<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CheckBox extends Component
{
    public $as;
    public $label;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $label = null)
    {
        $this->as    = $as;
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.check-box');
    }
}
