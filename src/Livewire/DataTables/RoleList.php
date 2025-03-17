<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Role;

class RoleList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'guard_name',
    ];

    protected string $model = Role::class;
}
