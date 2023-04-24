<?php

namespace FluxErp\Pipelines\Order;

use Closure;
use FluxErp\Models\Order;

class CreateInvoiceNumber
{
    public function handle(Order $order, Closure $next)
    {
        $order->getSerialNumber('invoice_number');
        $order->invoice_date = now();
        $order->is_locked = true;
        $order->state->transitionTo('open');
        $order->save();

        return $next($order);
    }
}
