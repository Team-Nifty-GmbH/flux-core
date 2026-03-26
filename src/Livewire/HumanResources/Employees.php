<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Actions\EmployeeDay\BulkCloseEmployeeDay;
use FluxErp\Livewire\DataTables\EmployeeList as BaseEmployeeList;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Employees extends BaseEmployeeList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::save as traitSave;
        DataTableHasFormEdit::edit as traitEdit;
    }

    #[DataTableForm]
    public EmployeeForm $employeeForm;

    public array $timeframe = [];

    public bool $isSelectable = true;

    protected ?string $includeBefore = 'flux::livewire.human-resources.employees';

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Bulk Close Employee Day'))
                ->when(fn () => resolve_static(BulkCloseEmployeeDay::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $wire.timeframe = [];
                    $modalOpen('close-employee-day-modal');
                JS),
        ];
    }

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

    public function closeEmployeeDay(): bool
    {
        try {
            BulkCloseEmployeeDay::make([
                'employees' => array_diff($this->selected, ['*']),
                'timeframe' => $this->timeframe,
            ])
                ->checkPermission()
                ->validate()
                ->executeAsync();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }
}
