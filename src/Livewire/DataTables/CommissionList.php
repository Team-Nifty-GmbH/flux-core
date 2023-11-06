<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Commission;
use TeamNiftyGmbH\DataTable\DataTable;

class CommissionList extends DataTable
{
    protected string $model = Commission::class;

    public array $enabledCols = [
        'user.name',
        'order.order_number',
        'order_position.name',
        'total_net_price',
        'commission_rate',
        'commission',
    ];

    public array $columnLabels = [
        'user.name' => 'Commission Agent',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public function mount(): void
    {
        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'commission_rate' => 'percentage',
            ]
        );
    }

    public function itemToArray($item): array
    {
        $item->commission_rate = $item->commission_rate['commission_rate'];

        return parent::itemToArray($item);
    }
}
