<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Commission;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

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

    public function mount(): void
    {
        $this->availableCols = ModelInfo::forModel($this->model)
            ->attributes
            ->pluck('name')
            ->toArray();

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
