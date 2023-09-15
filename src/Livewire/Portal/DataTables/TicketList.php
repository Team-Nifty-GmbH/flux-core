<?php

namespace FluxErp\Livewire\Portal\DataTables;

use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class TicketList extends DataTable
{
    protected string $model = Ticket::class;

    public array $enabledCols = [
        'title',
        'state',
        'description',
        'created_at',
    ];

    public array $sortable = ['*'];
}
