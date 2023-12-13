<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use FluxErp\Livewire\Forms\WorkTimeTypeForm;
use FluxErp\Models\WorkTimeType;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class WorkTimeTypes extends WorkTimeTypeList
{
    use Actions;

    public string $view = 'flux::livewire.settings.work-time-types';

    public WorkTimeTypeForm $workTimeType;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Work Time Types');
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(CreateWorkTimeType::canPerformAction(false))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(UpdateWorkTimeType::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit(record.id)']
                ),
        ];
    }

    public function edit(WorkTimeType $workTimeType): void
    {
        $this->workTimeType->reset();
        $this->workTimeType->fill($workTimeType);

        $this->js(<<<'JS'
            $openModal('edit-work-time-type');
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
}
