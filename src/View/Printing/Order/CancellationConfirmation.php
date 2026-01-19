<?php

namespace FluxErp\View\Printing\Order;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class CancellationConfirmation extends OrderView
{
    protected bool $showAlternatives = false;

    public function render(): View|Factory
    {
        return view('flux::printing.order.cancellation-confirmation', ['model' => $this->model]);
    }

    public function getSubject(): string
    {
        return __('Cancellation Confirmation') . ' ' . ($this->model->order_number ?: __('Preview'));
    }
}
