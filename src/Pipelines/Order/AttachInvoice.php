<?php

namespace FluxErp\Pipelines\Order;

use Closure;

class AttachInvoice
{
    public function handle($event, Closure $next)
    {
        $model = $event->model;
        $response = $event->response;
        $fileName = __('Invoice', locale: $model->addressInvoice?->language?->iso_code ?? app()->getLocale())
            . '_' . $model->invoice_number . '.pdf';

        $model->addMediaFromStream($response)
            ->usingName($fileName)
            ->usingFileName($fileName)
            ->toMediaCollection('invoice');

        return $next($event);
    }
}
