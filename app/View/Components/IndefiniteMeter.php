<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class IndefiniteMeter extends Component
{
    public $as;
    public $caption;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $caption = null)
    {
        if (is_null($as))
            $this->as = 'indef-meter-' . Str::random(4);
        else
            $this->as = $as;

        if (!is_null($caption))
            $this->caption = $caption;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.indefinite-meter');
    }
}
