<?php

namespace FluxErp\View\Printing\Order;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class FinalInvoice extends Invoice
{
    public function render(): View|Factory
    {
        return view('flux::printing.order.final-invoice', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getSubject(): string
    {
        return __('Final Invoice') . ' ' . ($this->model->invoice_number ?: __('Preview'));
    }
}
