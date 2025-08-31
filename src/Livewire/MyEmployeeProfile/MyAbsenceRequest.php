<?php

namespace FluxErp\Livewire\MyEmployeeProfile;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\AbsenceRequest\AbsenceRequest;
use FluxErp\Models\AbsenceRequest as AbsenceRequestModel;

class MyAbsenceRequest extends AbsenceRequest
{
    public function mount(AbsenceRequestModel $id): void
    {
        if ($id->employee_id !== auth()->user()?->employee?->getKey()) {
            abort(403);
        }

        parent::mount($id);
    }

    public function getEmployeeUrl(): string
    {
        return route('human-resources.my-employee-profile');
    }

    public function getTabs(): array
    {
        return collect(parent::getTabs())
            ->map(function (TabButton $tab) {
                if ($tab->component === 'absence-request.employee-days') {
                    $tab->component = 'my-employee-profile.absence-request.employee-days';
                }

                return $tab;
            })
            ->toArray();
    }
}
