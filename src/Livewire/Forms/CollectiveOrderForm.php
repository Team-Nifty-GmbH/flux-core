<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\CreateCollectiveOrder;

class CollectiveOrderForm extends FluxForm
{
    public ?int $order_type_id = null;

    public ?int $split_order_order_type_id = null;

    public array $orders = [];

    protected bool $asyncAction = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateCollectiveOrder::class,
        ];
    }
}
