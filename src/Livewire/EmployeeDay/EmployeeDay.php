<?php

namespace FluxErp\Livewire\EmployeeDay;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\EmployeeDayForm;
use FluxErp\Models\EmployeeDay as EmployeeDayModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EmployeeDay extends Component
{
    use Actions, WithTabs;

    public EmployeeDayForm $employeeDayForm;

    public array $queryString = [
        'tab' => ['except' => 'human-resources.employee-day-overview'],
    ];

    public string $tab = 'human-resources.employee-day-overview';

    public function mount(EmployeeDayModel $id): void
    {
        $this->employeeDayForm->fill($id);
    }

    public function render(): View
    {
        return view('flux::livewire.human-resources.employee-day-show');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('human-resources.employee-day-overview')
                ->text(__('Overview'))
                ->isLivewireComponent()
                ->wireModel('employeeDayForm'),
            TabButton::make('human-resources.employee-day.work-times')
                ->text(__('Work Times'))
                ->isLivewireComponent()
                ->wireModel('employeeDayForm.id'),
            TabButton::make('human-resources.employee-day.absence-requests')
                ->text(__('Absences'))
                ->isLivewireComponent()
                ->wireModel('employeeDayForm.id'),
            TabButton::make('human-resources.employee-day.comments')
                ->text(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('employeeDayForm.id'),
            TabButton::make('human-resources.employee-day.activities')
                ->text(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('employeeDayForm.id'),
        ];
    }
}
