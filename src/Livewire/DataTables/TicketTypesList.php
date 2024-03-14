<?php

namespace FluxErp\Livewire\DataTables;



use FluxErp\Models\TicketType;

class TicketTypesList extends BaseDataTable
{

    protected string $model = TicketType::class;

    public array $enabledCols = [
        'name',
    ];
}
