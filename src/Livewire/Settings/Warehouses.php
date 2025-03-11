<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use FluxErp\Livewire\DataTables\WarehouseList;
use FluxErp\Livewire\Forms\WarehouseForm;
use FluxErp\Models\Warehouse;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Warehouses extends WarehouseList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.warehouses';

    public WarehouseForm $warehouse;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
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
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateWarehouse::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteWarehouse::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Warehouse')]),
                ]),
        ];
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

    public function edit(Warehouse $warehouse): void
    {
        $this->warehouse->reset();
        $this->warehouse->fill($warehouse);

        $this->js(<<<'JS'
            $modalOpen('edit-warehouse-modal');
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
}
