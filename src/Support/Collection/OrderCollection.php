<?php

namespace FluxErp\Support\Collection;

use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Blade;

class OrderCollection extends Collection
{
    public function printLayouts(): array
    {
        return $this->reduce(
            fn (?array $carry, Order $order) => array_merge(
                $carry ?? [],
                $order->resolvePrintViews()
            )
        );
    }

    public function toMap(): BaseCollection
    {
        return $this->groupBy(fn (Order $order) => $order->addressDelivery?->latitude . ',' . $order->addressDelivery?->longitude)
            ->filter(fn (Collection $orders, string $key) => $key !== ',')
            ->map(function (Collection $orders) {
                $firstOrder = $orders->first();
                $address = $firstOrder->addressDelivery;

                return [
                    'id' => $orders->pluck('id')->implode(','),
                    'count' => $orders->count(),
                    'tooltip' => $this->buildTooltip($orders),
                    'popup' => $this->buildPopup($orders),
                    'icon' => $this->buildIcon($orders),
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ];
            })
            ->values();
    }

    protected function buildTooltip(Collection $orders): string
    {
        return '<div>' . $orders->first()->addressDelivery?->postal_address . (($count = $orders->count()) > 1 ? ' (' . $count . ' ' . __('Orders') . ')' : '') . '</div>';
    }

    protected function buildPopup(Collection $orders): string
    {
        return Blade::render(
            'flux::components.order.map-popup',
            ['orders' => $orders]
        );
    }

    protected function buildIcon(Collection $orders): string
    {
        return Blade::render(
            'flux::components.order.map-marker',
            ['count' => $orders->count()]
        );
    }
}
