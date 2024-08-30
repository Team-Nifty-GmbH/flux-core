<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ContactOrigin;

class ContactOriginList extends BaseDataTable
{
    protected string $model = ContactOrigin::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];
}
