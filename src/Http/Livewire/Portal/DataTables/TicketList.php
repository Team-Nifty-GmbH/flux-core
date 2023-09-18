<?php

namespace FluxErp\Http\Livewire\Portal\DataTables;

use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class TicketList extends DataTable
{
    protected string $model = Ticket::class;

    public array $enabledCols = [
        'title',
        'state',
        'ticket_type.name',
        'created_at',
    ];

    public array $sortable = ['*'];

    public function mount(): void
    {
        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('ticketType:id,name');
    }
}
