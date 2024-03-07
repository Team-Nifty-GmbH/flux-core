<?php

namespace FluxErp\Livewire\DataTables;

use TeamNiftyGmbH\DataTable\DataTable;

class BaseDataTable extends DataTable
{
    protected function getModel(): string
    {
        return resolve_static($this->model, 'class');
    }
}
