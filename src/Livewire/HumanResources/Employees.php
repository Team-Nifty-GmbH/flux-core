<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\DataTables\EmployeeList as BaseEmployeeList;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;

class Employees extends BaseEmployeeList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::save as traitSave;
        DataTableHasFormEdit::edit as traitEdit;
    }

    #[DataTableForm]
    public EmployeeForm $employeeForm;

    protected ?string $includeBefore = 'flux::livewire.human-resources.employees';

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        if ($id) {
            $this->redirectRoute(
                'human-resources.employees.id',
                ['id' => $id],
                navigate: true
            );

            return;
        }

        $this->traitEdit($id);
    }

    #[Renderless]
    public function save(): bool
    {
        $result = $this->traitSave();

        if ($result) {
            $this->edit($this->employeeForm->id);
        }

        return $result;
    }
}
