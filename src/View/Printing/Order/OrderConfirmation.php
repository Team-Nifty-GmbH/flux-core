<?php

namespace FluxErp\View\Printing\Order;

class OrderConfirmation extends OrderView
{
    public function getSubject(): string
    {
        return __('Order Confirmation') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
