<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Actions\Document\DownloadDocuments;
use FluxErp\Actions\EmployeeDay\BulkCloseEmployeeDay;
use FluxErp\Livewire\DataTables\EmployeeList as BaseEmployeeList;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Models\Employee;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
                    $tsui.open.modal('close-employee-day-modal');
                JS),
            DataTableButton::make()
                ->text(__('Download documents'))
                ->icon('arrow-down-tray')
                ->when(fn () => resolve_static(DownloadDocuments::class, 'canPerformAction', [false]))
                ->wireClick('downloadDocuments()'),
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

    #[Renderless]
    public function downloadDocuments(): ?StreamedResponse
    {
        try {
            return DownloadDocuments::make([
                'model_type' => morph_alias(Employee::class),
                'model_ids' => $this->getSelectedModelsQuery()->pluck($this->modelKeyName)->all(),
            ])
                ->checkPermission()
                ->validate()
                ->execute()
                ->toResponse(request());
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }
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
