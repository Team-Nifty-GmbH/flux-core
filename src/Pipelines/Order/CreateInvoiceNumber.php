<?php

namespace FluxErp\Pipelines\Order;

use Closure;
use FluxErp\Models\Order;
use FluxErp\States\Order\Draft;

class CreateInvoiceNumber
{
    public function handle(Order $order, Closure $next)
    {
        $order->getSerialNumber('invoice_number');
        $order->invoice_date = now();
        $order->is_locked = true;

        if ($order->state instanceof Draft) {
            $order->state->transitionTo('open');
        }

        $order->save();

        return $next($order);
    }
}
