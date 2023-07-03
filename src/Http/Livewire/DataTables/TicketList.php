<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class TicketList extends DataTable
{
    public array $enabledCols = [
        'ticket_number',
        'ticket_type.name',
        'user',
        'related',
        'title',
        'state',
        'created_at',
    ];

    public bool $showFilterInputs = true;

    public array $availableRelations = ['*'];

    protected string $model = Ticket::class;

    public array $sortable = ['*'];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(Ticket::class)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'ticketType:id,name',
            'authenticatable',
            'model',
        ]);
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);

        /** @var Ticket $item */
        if ($related = $item->morphTo('model')->getResults()) {
            $returnArray['related'] = method_exists($related, 'getLabel') ? $related->getLabel() : null;
        }

        $returnArray['user'] = $item->authenticatable?->getLabel();

        return $returnArray;
    }

    public function getFilterableColumns(?string $name = null): array
    {
        return $this->availableCols;
    }
}
