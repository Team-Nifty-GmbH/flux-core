<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Role;

class RoleList extends BaseDataTable
{
    protected string $model = Role::class;

    public array $enabledCols = [
        'name',
        'guard_name',
    ];
}
