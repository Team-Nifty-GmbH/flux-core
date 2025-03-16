<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use FluxErp\Livewire\Forms\WorkTimeTypeForm;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class WorkTimeTypes extends WorkTimeTypeList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.work-time-types';

    public WorkTimeTypeForm $workTimeType;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateWorkTimeType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateWorkTimeType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteWorkTimeType::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Work Time Type')]),
                ]),
        ];
    }

    public function delete(): bool
    {
        try {
            DeleteWorkTimeType::make($this->workTimeType->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(WorkTimeType $workTimeType): void
    {
        $this->workTimeType->reset();
        $this->workTimeType->fill($workTimeType);

        $this->js(<<<'JS'
            $modalOpen('edit-work-time-type-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->workTimeType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
