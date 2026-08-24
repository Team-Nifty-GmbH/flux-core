<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\WarehouseBin\CreateWarehouseBin;
use FluxErp\Actions\WarehouseBin\DeleteWarehouseBin;
use FluxErp\Actions\WarehouseBin\UpdateWarehouseBin;
use FluxErp\Livewire\DataTables\WarehouseBinList;
use FluxErp\Livewire\Forms\WarehouseBinForm;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class WarehouseBins extends WarehouseBinList
{
    public WarehouseBinForm $warehouseBin;

    protected ?string $includeBefore = 'flux::livewire.settings.warehouse-bins';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateWarehouseBin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
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
                ->when(resolve_static(UpdateWarehouseBin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteWarehouseBin::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Warehouse Bin')]),
                ]),
        ];
    }

    public function delete(WarehouseBin $warehouseBin): bool
    {
        $this->warehouseBin->reset();
        $this->warehouseBin->fill($warehouseBin);

        try {
            $this->warehouseBin->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(WarehouseBin $warehouseBin): void
    {
        $this->warehouseBin->reset();
        $this->warehouseBin->fill($warehouseBin);

        $this->modalOpen('edit-warehouse-bin-modal');
    }

    public function save(): bool
    {
        try {
            $this->warehouseBin->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'warehouses' => resolve_static(Warehouse::class, 'query')
                ->get(['id', 'name'])
                ->toArray(),
        ]);
    }
}
