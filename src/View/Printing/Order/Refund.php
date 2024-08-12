<?php

namespace FluxErp\View\Printing\Order;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Refund extends Invoice
{
    public function render(): View|Factory
    {
        return view('print::order.refund', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getSubject(): string
    {
        return __('Refund') . ' ' . ($this->model->invoice_number ?: __('Preview'));
    }
}
