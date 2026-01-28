<?php

namespace FluxErp\Support\Collection;

use FluxErp\Models\Address;
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
        return $this->mapWithAddress()
            ->groupBy(fn (array $item) => $this->getCoordinateKey(data_get($item, 'address')))
            ->filter(fn (BaseCollection $items, string $key) => $key !== ',')
            ->map(fn (BaseCollection $items) => [
                'id' => $items->pluck('order.id')->implode(','),
                'count' => $items->count(),
                'tooltip' => $this->buildTooltip($items),
                'popup' => $this->buildPopup($items),
                'icon' => $this->buildIcon($items),
                'latitude' => data_get($items->first(), 'address.latitude'),
                'longitude' => data_get($items->first(), 'address.longitude'),
            ])
            ->values();
    }

    protected function mapWithAddress(): BaseCollection
    {
        return $this->map(fn (Order $order): array => [
            'order' => $order,
            'address' => $order->mappableDeliveryAddress(),
        ]);
    }

    protected function getCoordinateKey(array|object|null $address): string
    {
        return data_get($address, 'latitude') . ',' . data_get($address, 'longitude');
    }

    protected function buildTooltip(BaseCollection $items): string
    {
        $address = data_get($items->first(), 'address');
        $postalAddress = $address instanceof Address
            ? $address->getDescription()
            : (data_get($address, 'postal_address') ?? trim(
                data_get($address, 'street') . ', ' .
                data_get($address, 'zip') . ' ' .
                data_get($address, 'city'),
                ', '
            ));

        $count = $items->count();

        return '<div>' . $postalAddress . ($count > 1 ? ' (' . $count . ' ' . __('Orders') . ')' : '') . '</div>';
    }

    protected function buildPopup(BaseCollection $items): string
    {
        return Blade::render(
            'flux::components.order.map-popup',
            ['orders' => $items]
        );
    }

    protected function buildIcon(BaseCollection $items): string
    {
        return Blade::render(
            'flux::components.order.map-marker',
            ['count' => $items->count()]
        );
    }
}
