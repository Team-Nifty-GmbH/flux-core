<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Traits\Livewire\Actions;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

abstract class BaseDataTable extends DataTable
{
    use Actions, HasEloquentListeners;

    protected function getModel(): string
    {
        return resolve_static($this->model, 'class');
    }
}
