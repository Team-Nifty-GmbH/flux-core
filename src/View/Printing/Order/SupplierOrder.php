<?php

namespace FluxErp\View\Printing\Order;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class SupplierOrder extends OrderView
{
    public function render(): View|Factory
    {
        return view('print::order.supplier-order', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getSubject(): string
    {
        return __('Supplier Order') . ' ' . $this->model->order_number;
    }
}
