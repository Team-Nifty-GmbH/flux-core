<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Contracts\SignablePrintView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class DeliveryNote extends OrderView implements SignablePrintView
{
    public function render(): Factory|View
    {
        return view('print::order.delivery-note', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getSubject(): string
    {
        return __('Delivery Note') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
