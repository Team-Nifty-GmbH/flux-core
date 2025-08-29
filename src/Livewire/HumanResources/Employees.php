<?php

namespace FluxErp\Livewire\HumanResources;

use Exception;
use FluxErp\Livewire\DataTables\EmployeeList as BaseEmployeeList;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Employees extends BaseEmployeeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public EmployeeForm $employeeForm;

    protected ?string $includeBefore = 'flux::livewire.human-resources.employees';

    protected function getRowActions(): array
    {
        // Nur den Edit-Button, keine weiteren Actions
        return [];
    }

    #[Renderless]
    public function editEmployee($id): void
    {
        $this->redirectRoute('human-resources.employees.id', ['id' => $id], navigate: true);
    }

    public function save(): void
    {
        if ($this->employeeForm->id) {
            // Bei Update normales Speichern im Modal
            parent::save();
        } else {
            // Bei neuem Employee speichern und dann zu Detail-View
            try {
                $result = $this->employeeForm->save();

                if ($result) {
                    $this->toast()
                        ->success(__('Employee created. Please complete the details.'))
                        ->send();

                    // Redirect zur Detail-View für vollständige Datenerfassung
                    $this->redirectRoute('human-resources.employee', ['id' => $result->id], navigate: true);
                }
            } catch (Exception $e) {
                exception_to_notifications($e, $this);
            }
        }
    }

    protected function getRowActionEditButton(): DataTableButton
    {
        // Override: Edit button führt zur Detail-View statt Modal zu öffnen
        return DataTableButton::make()
            ->text(__('Edit'))
            ->icon('pencil')
            ->color('primary')
            ->wireClick('editEmployee(record.id)');
    }
}
