<?php

namespace FluxErp\Livewire\DataTables;

use Illuminate\Support\Facades\App;
use TeamNiftyGmbH\DataTable\DataTable;

class BaseDataTable extends DataTable
{
    protected function getModel(): string
    {
        return App::getAlias($this->model);
    }
}
