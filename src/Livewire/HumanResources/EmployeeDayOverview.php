<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\Forms\EmployeeDayForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class EmployeeDayOverview extends Component
{
    #[Modelable]
    public ?EmployeeDayForm $employeeDayForm = null;

    public function render(): View
    {
        return view('flux::livewire.human-resources.employee-day-overview');
    }
}
