<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\StorageArea\CreateStorageArea;
use FluxErp\Actions\StorageArea\DeleteStorageArea;
use FluxErp\Actions\StorageArea\UpdateStorageArea;
use FluxErp\Livewire\DataTables\StorageAreaList;
use FluxErp\Livewire\Forms\StorageAreaForm;
use FluxErp\Models\Warehouse;
use FluxErp\Models\StorageArea;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class StorageAreas extends StorageAreaList
{
    public StorageAreaForm $storageArea;

    protected ?string $includeBefore = 'flux::livewire.settings.storage-areas';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateStorageArea::class, 'canPerformAction', [false]))
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
                ->when(resolve_static(UpdateStorageArea::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteStorageArea::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Storage Area')]),
                ]),
        ];
    }

    public function delete(StorageArea $storageArea): bool
    {
        $this->storageArea->reset();
        $this->storageArea->fill($storageArea);

        try {
            $this->storageArea->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(StorageArea $storageArea): void
    {
        $this->storageArea->reset();
        $this->storageArea->fill($storageArea);

        $this->modalOpen('edit-storage-area-modal');
    }

    public function save(): bool
    {
        try {
            $this->storageArea->save();
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
