<?php

namespace FluxErp\Pipelines\Order;

use FluxErp\Models\Order;
use Closure;

class CreateInvoiceNumber
{
    public function handle(Order $order, Closure $next)
    {
        $order->getSerialNumber('invoice_number');

        return $next($order);
    }
}
