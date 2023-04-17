<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Events\Print\PdfCreatedEvent;
use FluxErp\Events\Print\PdfCreatingEvent;
use FluxErp\Models\Order;
use FluxErp\Pipelines\Order\AttachInvoice;
use FluxErp\Pipelines\Order\CreateInvoiceNumber;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Retoure extends OrderView
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        // Set locale to addressInvoice language if it is set
        app()->setLocale($order->addressInvoice?->language?->iso_code ?? config('app.locale'));

        $this->model = $order->load(['parent', 'orderType']);

        $this->prepareModel();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory
    {
        return view('print::order.retoure', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }
}
