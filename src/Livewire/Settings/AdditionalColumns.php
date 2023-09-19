<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\AdditionalColumnList;
use FluxErp\Models\AdditionalColumn;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AdditionalColumns extends AdditionalColumnList
{
    protected string $view = 'flux::livewire.settings.additional-columns';

    public bool $showAdditionalColumnModal = false;

    public bool $create = true;

    protected $listeners = [
        'closeModal',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.show(record)',
                ]),
        ];
    }

    public function show(AdditionalColumn $record = null): void
    {
        $this->dispatch('show', $record?->toArray())->to('settings.additional-column-edit');

        $this->create = ! $record->exists;
        $this->showAdditionalColumnModal = true;
    }

    public function closeModal(): void
    {
        $this->loadData();

        $this->showAdditionalColumnModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.additional-column-edit');
    }
}
