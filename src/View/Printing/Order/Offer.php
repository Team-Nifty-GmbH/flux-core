<?php

namespace FluxErp\View\Printing\Order;

class Offer extends OrderView
{
    public function getSubject(): string
    {
        return __('Offer') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
