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

class Offer extends OrderView
{
    public string $title;

    public function __construct(\FluxErp\Models\Order $order)
    {
        parent::__construct($order);

        $this->title = __('Offer') . ' ' . $this->model->order_number;
    }
}
