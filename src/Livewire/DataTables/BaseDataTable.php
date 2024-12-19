<?php

namespace FluxErp\Livewire\DataTables;

use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

abstract class BaseDataTable extends DataTable
{
    use HasEloquentListeners;

    protected function getModel(): string
    {
        return resolve_static($this->model, 'class');
    }
}
