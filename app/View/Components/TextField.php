<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class TextField extends Component
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
        $this->as = !is_null($as) ? $as : 'input-' . Str::random(6);
        $this->default = $default;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.text-field');
    }
}
