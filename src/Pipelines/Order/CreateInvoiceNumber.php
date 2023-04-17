<?php

namespace FluxErp\Pipelines\Order;

use FluxErp\Models\Order;
use Closure;

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
