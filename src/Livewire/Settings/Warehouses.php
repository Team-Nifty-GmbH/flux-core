<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use FluxErp\Livewire\DataTables\WarehouseList;
use FluxErp\Livewire\Forms\WarehouseForm;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Warehouses extends WarehouseList
{
    use Actions;

    public string $view = 'flux::livewire.settings.warehouses';

    public WareHouseForm $warehouseForm;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Warehouses');
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(CreateWarehouse::canPerformAction(false))
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
                ->when(UpdateWarehouse::canPerformAction(false))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
        ];
    }

    public function edit(Warehouse $warehouse): void
    {
        $this->warehouseForm->reset();
        $this->warehouseForm->fill($warehouse);

        $this->js(<<<'JS'
            $openModal('edit-warehouse');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->warehouseForm->save();
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
            $this->warehouseForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
