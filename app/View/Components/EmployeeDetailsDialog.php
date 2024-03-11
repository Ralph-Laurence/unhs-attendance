<?php

namespace App\View\Components;

use App\Http\Utils\RouteNames;
use App\Models\Employee;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class EmployeeDetailsDialog extends Component
{
    public $as;
    public $modalLabel;
    public $modalFor;

    private $modalIcon;
    private $modalTitle;
    private $employeeRole;

    public $datasource;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct( $modalFor, $datasource, $as = null)
    {
        if (empty($as))
            $this->as = 'employeeDetailsModal-' . Str::random(6);
        else
            $this->as = $as;

        $this->datasource = $datasource;

        $this->modalLabel = $as . 'Label';

        $this->modalTitle = $this->getTitle($modalFor);
        $this->modalIcon  = $this->getIcon($modalFor);
        
        $this->employeeRole = $this->describeRole($modalFor);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $renderElements = [
            'modalIcon'     => $this->modalIcon,
            'modalTitle'    => $this->modalTitle,
            'employeeRole'  => $this->employeeRole,

            'viewDtrRoute'  => route(RouteNames::DailyTimeRecord['index'])
        ];

        return view('components.employee-details-dialog', $renderElements);
    }

    private function getIcon($modalFor)
    {
        $icons = [
            'default'               => asset('images/internal/icons/modal_icon_employee.png'),
            Employee::RoleTeacher   => asset('images/internal/icons/modal_icon_teacher.png'),
            Employee::RoleStaff     => asset('images/internal/icons/modal_icon_staff.png'),
        ];

        if (!array_key_exists($modalFor, $icons))
            return $icons['default'];

        return $icons[$modalFor];
    }

    private function getTitle($modalFor)
    {
        $title = $this->describeRole($modalFor). ' Information';

        return $title;
    }

    private function getRoles()
    {
        return [
            'default'               => Employee::STR_COLLECTIVE_ROLE_ALL,
            Employee::RoleTeacher   => Employee::STR_COLLECTIVE_ROLE_FACULTY,
            Employee::RoleStaff     => Employee::STR_ROLE_STAFF
        ];
    }

    private function describeRole($role)
    {
        $roles = $this->getRoles();

        if (!array_key_exists($role, $roles))
            return $roles['default'];

        return $roles[$role];
    }
}
