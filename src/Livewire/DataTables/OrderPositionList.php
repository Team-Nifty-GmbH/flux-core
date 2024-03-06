<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Builder;

class OrderPositionList extends BaseDataTable
{
    protected string $model = OrderPosition::class;

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
                'unit_net_price' => 'coloredMoney',
                'unit_gross_price' => 'coloredMoney',
                'total_net_price' => 'coloredMoney',
                'total_gross_price' => 'coloredMoney',
                'total_base_net_price' => 'coloredMoney',
                'total_base_gross_price' => 'coloredMoney',
                'margin' => 'coloredMoney',
            ]
        );
    }
}
