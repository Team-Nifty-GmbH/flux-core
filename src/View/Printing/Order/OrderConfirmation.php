<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Contracts\SignablePrintView;

class OrderConfirmation extends OrderView implements SignablePrintView
{
    public function getSubject(): string
    {
        return __('Order Confirmation') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
