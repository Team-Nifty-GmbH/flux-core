<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\PriceList;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ProformaInvoice extends OrderView
{
    protected bool $showAlternatives = false;

    public function render(): View|Factory
    {
        return view('print::order.proforma-invoice', [
            'model' => $this->model,
            'summary' => $this->summary,
            'isNet' => $this->isNet(),
        ]);
    }

    public function getSubject(): string
    {
        return __('Proforma Invoice') . ' ' . ($this->model->order_number ?: __('Preview'));
    }

    protected function isNet(): bool
    {
        return ($this->model->priceList ?? resolve_static(PriceList::class, 'default'))->is_net;
    }
}
