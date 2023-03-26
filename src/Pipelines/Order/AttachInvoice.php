<?php

namespace FluxErp\Pipelines\Order;

use Closure;

class AttachInvoice
{
    public function handle($event, Closure $next)
    {
        $model = $event->model;
        $response = $event->response;

        $model->addMediaFromStream($response)
            ->usingName(
                __('Invoice', locale: $model->addressInvoice?->language?->iso_code ?? app()->getLocale())
                . '_' . $model->invoice_number . '.pdf'
            )
            ->toMediaCollection('invoice');

        return $next($event);
    }
}
