<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\States\Order\Draft;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Invoice extends OrderView
{
    public function render(): View|Factory
    {
        return view('print::order.invoice', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return __('Invoice') . ' ' . ($this->model->invoice_number ?: __('Preview'));
    }

    public function beforePrinting(): void
    {
        if ($this->preview || $this->model->invoice_number) {
            return;
        }

        $this->model->getSerialNumber('invoice_number');
        $this->model->invoice_date = now();
        $this->model->is_locked = true;

        if ($this->model->state instanceof Draft) {
            $this->model->state->transitionTo('open');
        }

        $this->model->save();
    }

    public function afterPrinting(): void
    {
        if ($this->preview) {
            return;
        }

        $this->attachToModel();
    }
}
