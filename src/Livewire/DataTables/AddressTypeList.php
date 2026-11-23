<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AddressType;

class AddressTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'tenants.name',
        'address_type_code',
        'is_locked',
    ];

    protected string $model = AddressType::class;
}
