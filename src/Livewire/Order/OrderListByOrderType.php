<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Order\OrderList as BaseOrderList;
use FluxErp\Models\OrderType;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderListByOrderType extends BaseOrderList
{
    public OrderForm $order;

    public function mount(): void
    {
        parent::mount();

        $this->filters = [
            [
                'column' => 'order_type_id',
                'operator' => '=',
                'value' => $this->orderType,
            ],
        ];
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('New order'))
                ->icon('plus')
                ->when(! resolve_static(OrderType::class, 'query')
                    ->whereKey($this->orderType)
                    ->value('is_hidden')
                    && resolve_static(CreateOrder::class, 'canPerformAction', [false])
                )
                ->attributes([
                    'x-on:click' => "\$modalOpen('create-order-modal')",
                ]),
        ];
    }

    public function save(): ?false
    {
        $this->order->order_type_id = $this->orderType;

        return parent::save();
    }
}
