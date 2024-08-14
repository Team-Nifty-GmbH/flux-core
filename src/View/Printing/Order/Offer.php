<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Contracts\SignablePrintView;

class Offer extends OrderView implements SignablePrintView
{
    public function getSubject(): string
    {
        return __('Offer').' '.($this->model->order_number ?: __('Preview'));
    }
}
