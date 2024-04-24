<?php

namespace App\View\Components;

use Illuminate\View\Component;
use illuminate\Support\Str;

class Fab extends Component
{
    public $as;
    public $icon;
    public $tint;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($as = null, $icon = null, $tint = null)
    {
        if (is_null($as))
            $this->as = 'indef-meter-' . Str::random(4);
        else
            $this->as = $as;

        if (is_null($icon))
            $this->icon = 'fa-plus';
        else
            $this->icon = $icon;

        if (is_null($tint))
            $this->tint = 'fab-primary';
        else
            $this->tint = $tint;

        switch($tint)
        {
            case 'danger':
                $this->tint = 'fab-danger';
                break;

            case 'warning':
                $this->tint = 'fab-warning';
                break;

            case 'accent':
                $this->tint = 'fab-accent';
                break;

            case 'primary':
            default:
                $this->tint = 'fab-primary';
                break;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.fab');
    }
}
