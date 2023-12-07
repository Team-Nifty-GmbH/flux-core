<?php

namespace FluxErp\View\Printing\Order;

class Offer extends OrderView
{
    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return __('Offer') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
