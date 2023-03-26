<?php

namespace FluxErp\Http\Livewire\DataTables;

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

    public array $availableRelations = ['*'];

    protected string $model = \FluxErp\Models\Ticket::class;

    public array $sortable = ['*'];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel(\FluxErp\Models\Ticket::class)->attributes;

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

        /** @var \FluxErp\Models\Ticket $item */
        if ($related = $item->morphTo('model')->getResults()) {
            $returnArray['related'] = method_exists($related, 'getLabel') ? $related->getLabel() : null;
        }

        $returnArray['user'] = $item->authenticatable?->getLabel();

        return $returnArray;
    }
}
