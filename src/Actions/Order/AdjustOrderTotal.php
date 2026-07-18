<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\AdjustOrderTotalRuleset;
use Illuminate\Validation\ValidationException;

class AdjustOrderTotal extends FluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return AdjustOrderTotalRuleset::class;
    }

    public function performAction(): Order
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->with('orderType:id,order_type_enum')
            ->firstOrFail();

        $position = $order->orderPositions()->sole();

        // Order totals are stored signed by the order type's multiplier (e.g. Purchase
        // orders store negative totals). Counteract it so the resulting gross total
        // matches the given amount as-is, regardless of the order type.
        $signedTotal = bcmul(
            $this->getData('total_gross_price'),
            $order->orderType->order_type_enum->multiplier()
        );

        UpdateOrderPosition::make([
            'id' => $position->getKey(),
            'order_id' => $order->getKey(),
            'discount_percentage' => null,
            'unit_price' => gross_to_net(
                bcdiv($signedTotal, $position->amount ?: 1, 9),
                $position->vat_rate_percentage ?? 0
            ),
            'is_net' => true,
        ])
            ->validate()
            ->execute();

        $order->refresh()
            ->calculatePrices()
            ->calculateBalance()
            ->save();

        return $order->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->withCount('orderPositions')
            ->with('createdFrom.orderType:id,order_type_enum')
            ->first(['id', 'created_from_id', 'is_locked']);

        if (
            is_null($order?->created_from_id)
            || ! $order->createdFrom?->orderType?->order_type_enum?->isSubscription()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Only generated subscription orders can be adjusted.')],
            ])->errorBag('adjustOrderTotal');
        }

        if ($order->is_locked) {
            throw ValidationException::withMessages([
                'id' => [__('Locked orders cannot be adjusted.')],
            ])->errorBag('adjustOrderTotal');
        }

        if ($order->order_positions_count !== 1) {
            throw ValidationException::withMessages([
                'id' => [__('Only orders with exactly one position can be adjusted.')],
            ])->errorBag('adjustOrderTotal');
        }
    }
}
