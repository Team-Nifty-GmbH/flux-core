<?php

namespace FluxErp\Livewire\Portal\DataTables;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class TicketList extends BaseDataTable
{
    public array $availableCols = [
        'title',
        'description',
        'ticket_number',
        'state',
        'ticket_type.name',
        'created_at',
    ];

    public array $availableRelations = [];

    public array $columnLabels = [
        'ticket_type.name' => 'Ticket type',
    ];

    public array $enabledCols = [
        'title',
        'state',
        'ticket_type.name',
        'created_at',
    ];

    public array $sortable = ['*'];

    protected string $model = Ticket::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'ticketType:id,name',
        ]);
    }
}
