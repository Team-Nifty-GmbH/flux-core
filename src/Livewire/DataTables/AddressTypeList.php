<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AddressType;

class AddressTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'client.name',
        'address_type_code',
        'is_locked',
        'is_unique',
    ];

    protected string $model = AddressType::class;
}
