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

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public array $enabledCols = [
        'product_number',
        'order.order_number',
        'order.invoice_number',
        'name',
        'amount',
        'total_net_price',
    ];

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->orderBy('order_id');
    }

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'total_net_price' => 'coloredMoney',
                'total_gross_price' => 'coloredMoney',
                'margin' => 'coloredMoney',
            ]
        );
    }
}
