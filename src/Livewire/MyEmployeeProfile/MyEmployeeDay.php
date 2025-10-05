<?php

namespace FluxErp\Livewire\MyEmployeeProfile;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\EmployeeDay\EmployeeDay as BaseEmployeeDay;
use FluxErp\Models\EmployeeDay;

class MyEmployeeDay extends BaseEmployeeDay
{
    public function mount(EmployeeDay $id): void
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
}
