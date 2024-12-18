<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class DropList extends Component
{
    public $as;
    public $text;
    public $items;
    public $default;
    public $required;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $text = null, $items = null, $default = null, $required = null)
    {
        $this->as = !is_null($as) ? $as : 'input-' . Str::random(6);

        if (is_null($text))
            $this->text = 'Select';
        else
            $this->text = $text;

        $this->default = $default;
        $this->items   = $items;

        if ($required)
            $this->required = 'required';
        else
            $this->required = '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.drop-list');
    }
}
