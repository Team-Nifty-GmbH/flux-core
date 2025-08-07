<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\UserList;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class HrUserSettings extends UserList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public UserForm $userForm;

    public array $enabledCols = [
        'name',
        'email',
        'employee_number',
        'work_time_model.name',
        'location.name',
        'salary',
        'vacation_days_current',
        'employment_date',
    ];

    protected ?string $includeBefore = 'flux::livewire.settings.hr-user-settings-modal';

    protected function getTableActions(): array
    {
        return [];
    }
}