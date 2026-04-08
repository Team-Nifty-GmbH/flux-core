<?php

namespace FluxErp\Traits\Livewire\DataTable;

use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Livewire\RecordMerging;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

trait AllowRecordMerging
{
    protected bool $allowRecordMerging = true;

    #[Renderless]
    public function dispatchRecordMerging(): void
    {
        $this->dispatch('show-record-merging',
            recordIds: $this->selected,
            modelClass: $this->model,
        )->to(RecordMerging::class);
    }

    protected function getSelectedActionsAllowRecordMerging(): array
    {
        if ($this->allowRecordMerging === false) {
            return [];
        }

        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Record merging'))
                ->attributes([
                    'wire:click' => 'dispatchRecordMerging',
                    'x-show' => '$wire.selected.length > 1',
                ])
                ->when(resolve_static(MergeRecords::class, 'canPerformAction', [false])),
        ];
    }
}
