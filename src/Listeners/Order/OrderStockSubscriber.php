<?php

namespace FluxErp\Listeners\Order;

use FluxErp\Actions\Printing;
use FluxErp\Actions\StockPosting\CreateStockPostingsFromOrder;
use FluxErp\Models\Order;

class OrderStockSubscriber
{
    public function handle(Printing $event): void
    {
        if ($event->getData('model_type') !== morph_alias(Order::class)) {
            return;
        }

        $order = resolve_static(Order::class, 'query')
            ->whereKey($event->getData('model_id'))
            ->with([
                'orderType:id,post_stock_print_layouts,reserve_stock_print_layouts',
            ])
            ->first(['id', 'order_type_id']);

        if (! $order
            || $event->printable->preview
            || ! in_array(
                $event->getData('view'),
                array_merge(
                    $order->orderType->post_stock_print_layouts ?? [],
                    $order->orderType->reserve_stock_print_layouts ?? []
                )
            )
        ) {
            return;
        }

        CreateStockPostingsFromOrder::make([
            'id' => $order->id,
            'only_reserve_stock' => ! in_array(
                $event->getData('view'),
                $order->orderType->post_stock_print_layouts
            ),
        ])
            ->checkPermission()
            ->validate()
            ->execute();
    }

    public function subscribe(): array
    {
        return [
            'action.executing: ' . Printing::class => 'handle',
        ];
    }
}
