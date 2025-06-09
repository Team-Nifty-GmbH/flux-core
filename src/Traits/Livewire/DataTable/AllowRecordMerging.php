<?php

namespace FluxErp\Traits\Livewire\DataTable;

use FluxErp\Actions\Record\MergeRecords;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

trait AllowRecordMerging
{
    protected bool $allowRecordMerging = true;

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
                    'wire:click' => '$dispatchTo(\'record-merging\', \'show-record-merging\', '
                        . '{ recordIds: $wire.selected, modelClass: \'' . addslashes($this->model) . '\' })',
                    'x-show' => '$wire.selected.length > 1',
                ])
                ->when(resolve_static(MergeRecords::class, 'canPerformAction', [false])),
        ];
    }
}
