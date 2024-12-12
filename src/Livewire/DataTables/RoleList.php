<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Role;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class RoleList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Role::class;

    public array $enabledCols = [
        'name',
        'guard_name',
    ];
}
