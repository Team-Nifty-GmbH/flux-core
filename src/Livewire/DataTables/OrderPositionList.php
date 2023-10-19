<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class OrderPositionList extends DataTable
{
    protected string $model = OrderPosition::class;

    public bool $showFilterInputs = true;

    public array $enabledCols = [
        'product_number',
        'order.order_number',
        'order.invoice_number',
        'name',
        'amount',
        'total_net_price',
    ];

    public array $aggregatable = [
        'total_net_price',
        'total_gross_price',
        'amount',
    ];

    public array $sortable = ['*'];

    public array $availableRelations = ['*'];

    public function mount(): void
    {
        $this->availableCols = ModelInfo::forModel(OrderPosition::class)
            ->attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->orderBy('order_id');
    }
}
