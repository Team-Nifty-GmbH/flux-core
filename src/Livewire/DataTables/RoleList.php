<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Role;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class RoleList extends BaseDataTable
{
    use HasEloquentListeners;

    protected string $model = Role::class;

    public array $enabledCols = [
        'name',
        'guard_name',
    ];
}
