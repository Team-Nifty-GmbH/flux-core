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

    public WareHouseForm $warehouse;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Warehouses');
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreateWarehouse::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateWarehouse::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
        ];
    }

    public function edit(Warehouse $warehouse): void
    {
        $this->warehouse->reset();
        $this->warehouse->fill($warehouse);

        $this->js(<<<'JS'
            $openModal('edit-warehouse');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->warehouse->save();
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
            $this->warehouse->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
