<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class DropList extends Component
{
    public $as;
    public $text;
    public $items;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $text = null, $items = null)
    {
        $this->as = !is_null($as) ? $as : 'input-' . Str::random(6);

        if (is_null($text))
            $this->text = 'Select';

        $this->items = $items;
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
