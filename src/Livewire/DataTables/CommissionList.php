<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Commission;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class CommissionList extends DataTable
{
    protected string $model = Commission::class;

    public array $enabledCols = [
        'user.name',
        'order.order_number',
        'order.address_invoice.name',
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
        parent::mount();

        $this->formatters = array_merge(
            $this->formatters,
            [
                'commission_rate' => 'percentage',
                'commission' => 'coloredMoney',
                'total_net_price' => 'coloredMoney',
            ]
        );
    }

    public function itemToArray($item): array
    {
        $item->commission_rate = $item->commission_rate['commission_rate'];

        return parent::itemToArray($item);
    }

    public function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'order_id',
            ]
        );
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make(label: __('View Order'))
                ->color('primary')
                ->icon('eye')
                ->href('#')
                ->attributes([
                    'x-bind:href' => '\'/orders/\' + record.order_id',
                ]),
        ];
    }
}
