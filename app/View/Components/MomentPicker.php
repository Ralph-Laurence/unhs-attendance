<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MomentPicker extends Component
{
    public $as;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null)
    {
        $this->as = !is_null($as) ? $as : 'input-' . Str::random(6);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.moment-picker');
    }
}
