<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Commission;
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
        'order.order_number' => 'Order Number',
        'order_position.name' => 'Order Position',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
