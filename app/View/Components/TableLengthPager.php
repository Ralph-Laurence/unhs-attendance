<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class TableLengthPager extends Component
{
    public $as;
    private const Lengths = [
        '10' => 10,
        '25' => 25,
        '50' => 50,
        '100' => 100,
    ];

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
        return view('components.table-length-pager', [
            'items'     => self::Lengths,
            'default'   => '10'
        ]);
    }
}
