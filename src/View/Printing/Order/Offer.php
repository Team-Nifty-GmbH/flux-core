<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;

class Offer extends OrderView
{
    public function __construct(Order $order)
    {
        parent::__construct($order);

        $this->title = __('Offer') . ' ' . $this->model->order_number;
    }
}
