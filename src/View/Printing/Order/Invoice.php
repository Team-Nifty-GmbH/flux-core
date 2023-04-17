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

class Invoice extends \FluxErp\View\Printing\Order\OrderView
{
    public static array $pipelines = [
        PdfCreatingEvent::class => [
            CreateInvoiceNumber::class,
        ],
        PdfCreatedEvent::class => [
            AttachInvoice::class,
        ],
    ];

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory
    {
        return view('print::order.invoice', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }
}
