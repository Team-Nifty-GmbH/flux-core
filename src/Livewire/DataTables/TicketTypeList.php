<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\TicketType;

class TicketTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'name',
    ];

    protected string $model = TicketType::class;
}
