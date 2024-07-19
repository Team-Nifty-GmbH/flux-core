<?php

namespace FluxErp\Livewire\Portal\DataTables;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class TicketList extends BaseDataTable
{
    protected string $model = Ticket::class;

    public array $columnLabels = [
        'ticket_type.name' => 'Ticket type',
    ];

    public array $enabledCols = [
        'title',
        'state',
        'ticket_type.name',
        'created_at',
    ];

    public array $availableCols = [
        'title',
        'description',
        'ticket_number',
        'state',
        'ticket_type.name',
        'created_at',
    ];

    public array $sortable = ['*'];

    public array $availableRelations = [];

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'ticketType:id,name',
        ]);
    }
}
