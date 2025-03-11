<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ContactOrigin;

class ContactOriginList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'is_active',
    ];

    protected string $model = ContactOrigin::class;
}
