<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Discount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\Order\ReplicateOrderRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ReplicateOrder extends FluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return ReplicateOrderRuleset::class;
    }

    public function performAction(): Order
    {
        $getOrderPositionsFromOrigin = is_null(data_get($this->data, 'order_positions'));

        $orderTypeEnum = resolve_static(OrderType::class, 'query')
            ->whereKey($this->getData('order_type_id'))
            ->value('order_type_enum');

        $originalOrder = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->when(
                $getOrderPositionsFromOrigin,
                fn (Builder $query) => $query->with([
                    'orderPositions' => fn (HasMany $query) => $query
                        ->where('is_bundle_position', false)
                        ->orderBy('slug_position'),
                ])
            )
            ->firstOrFail();

        $orderData = array_merge(
            $originalOrder->toArray(),
            $this->getData(),
        );

        unset(
            $orderData['id'],
            $orderData['uuid'],
            $orderData['agent_id'],
            $orderData['contact_bank_connection_id'],
            $orderData['address_invoice'],
            $orderData['address_delivery'],
            $orderData['state'],
            $orderData['payment_state'],
            $orderData['delivery_state'],
            $orderData['payment_reminder_current_level'],
            $orderData['payment_reminder_next_date'],
            $orderData['invoice_number'],
            $orderData['invoice_date'],
            $orderData['order_number'],
            $orderData['order_date'],
            $orderData['is_locked'],
            $orderData['is_imported'],
            $orderData['is_confirmed'],
            $orderData['is_paid'],
        );

        if ($originalOrder['parent_id'] === $orderData['parent_id']) {
            unset($orderData['parent_id']);
        }

        if (
            $originalOrder['contact_id'] === $orderData['contact_id']
            && in_array($orderTypeEnum, [OrderTypeEnum::SplitOrder, OrderTypeEnum::Retoure])
        ) {
            $orderData['parent_id'] = data_get($originalOrder, 'id');
        }

        if ($originalOrder['contact_id'] !== $orderData['contact_id']) {
            $orderData['vat_rate_id'] = null;
        }

        $orderData['created_from_id'] = data_get($originalOrder, 'id');

        $order = CreateOrder::make($orderData)
            ->checkPermission()
            ->validate()
            ->execute();

        $this->replicateDiscounts(morph_alias(Order::class), $originalOrder->getKey(), $order->getKey());

        if (! $getOrderPositionsFromOrigin) {
            $replicateOrderPositions = collect($this->data['order_positions']);
            $orderPositions = resolve_static(OrderPosition::class, 'query')
                ->whereKey(array_column($this->data['order_positions'], 'id'))
                ->where('is_bundle_position', false)
                ->orderBy('slug_position')
                ->get()
                ->map(function (OrderPosition $orderPosition) use ($replicateOrderPositions, $orderTypeEnum): OrderPosition {
                    $position = $replicateOrderPositions->first(
                        fn (array $item): bool => data_get($item, 'id') === $orderPosition->getKey()
                    );

                    if (in_array($orderTypeEnum, [OrderTypeEnum::SplitOrder, OrderTypeEnum::Retoure])) {
                        $orderPosition->origin_position_id = $orderPosition->getKey();
                    }

                    $orderPosition->original_amount = $orderPosition->amount;
                    $orderPosition->amount = data_get($position, 'amount');

                    return $orderPosition;
                })
                ->toArray();
        } else {
            if (in_array($orderTypeEnum, [OrderTypeEnum::SplitOrder, OrderTypeEnum::Retoure])) {
                $orderPositions = array_map(
                    function (array $position): array {
                        $position['origin_position_id'] = data_get($position, 'id');

                        return $position;
                    },
                    $originalOrder->orderPositions?->toArray() ?? []
                );
            } else {
                $orderPositions = $originalOrder->orderPositions?->toArray() ?? [];
            }
        }

        $newOrderPositions = collect();
        foreach ($orderPositions as $orderPosition) {
            $orderPosition['order_id'] = $order->id;

            if (data_get($orderPosition, 'parent_id')) {
                $orderPosition['parent_id'] = $newOrderPositions
                    ->firstWhere('origin_position_id', $orderPosition['parent_id'])
                    ?->getKey();
            }

            $originalPositionId = data_get($orderPosition, 'id');
            $orderPosition['created_from_id'] = $originalPositionId;

            if (
                ! data_get($orderPosition, 'is_free_text')
                && is_null(data_get($orderPosition, 'discount_percentage'))
            ) {
                $originalTotal = data_get($orderPosition, 'is_net')
                    ? bcabs(data_get($orderPosition, 'total_net_price') ?? 0)
                    : bcabs(data_get($orderPosition, 'total_gross_price') ?? 0);

                $originalAmount = data_get($orderPosition, 'original_amount')
                    ?? data_get($orderPosition, 'amount') ?? 0;

                $expectedOriginalTotal = data_get($orderPosition, 'is_net')
                    ? bcmul(
                        data_get($orderPosition, 'unit_net_price') ?? 0,
                        $originalAmount
                    )
                    : bcmul(
                        data_get($orderPosition, 'unit_gross_price') ?? 0,
                        $originalAmount
                    );

                if (bccomp($expectedOriginalTotal, 0) === 1
                    && bccomp($expectedOriginalTotal, $originalTotal) !== 0
                ) {
                    $orderPosition['discount_percentage'] = diff_percentage($expectedOriginalTotal, $originalTotal);
                }
            }

            unset(
                $orderPosition['id'],
                $orderPosition['uuid'],
                $orderPosition['sort_number'],
                $orderPosition['amount_packed_products'],
                $orderPosition['original_amount'],
                $orderPosition['total_net_price'],
                $orderPosition['total_gross_price'],
            );

            $orderPosition['signed_amount'] = bcmul(
                data_get($orderPosition, 'amount') ?? 0,
                $orderTypeEnum->multiplier()
            );

            if (! data_get($orderPosition, 'is_free_text')) {
                $orderPosition['unit_price'] = data_get($orderPosition, 'is_net')
                    ? data_get($orderPosition, 'unit_net_price')
                    : data_get($orderPosition, 'unit_gross_price');
            }

            $newPosition = CreateOrderPosition::make($orderPosition)
                ->checkPermission()
                ->validate()
                ->execute();

            $newOrderPositions->push($newPosition);

            $this->replicateDiscounts(morph_alias(OrderPosition::class), $originalPositionId, $newPosition->getKey());
        }

        $order->calculatePrices()->save();

        if ($this->getData('set_new_as_parent')) {
            $originalOrder->parent_id = $order->id;
            $originalOrder->save();
        }

        return $order->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $orderPositions = data_get($this->data, 'order_positions', []);
        $ids = array_column($orderPositions ?? [], 'id');

        if ($this->getData('set_new_as_parent')
            && resolve_static(Order::class, 'query')
                ->whereKey($this->getData('id'))
                ->whereNotNull('parent_id')
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'set_new_as_parent' => ['The given order already has a parent.'],
            ])
                ->errorBag('replicateOrder');
        }

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'order_positions' => ['No duplicate order position ids allowed.'],
            ])
                ->errorBag('replicateOrder');
        }

        if ($orderPositions) {
            if (resolve_static(OrderPosition::class, 'query')
                ->whereKey($ids)
                ->where('order_id', '!=', $this->data['id'])
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'order_positions' => ['Only order positions from given order allowed.'],
                ])
                    ->errorBag('replicateOrder');
            }
        }
    }

    private function replicateDiscounts(string $modelType, int $fromModelId, int $toModelId): void
    {
        resolve_static(Discount::class, 'query')
            ->where('model_type', $modelType)
            ->where('model_id', $fromModelId)
            ->get()
            ->each(function (Discount $discount) use ($modelType, $toModelId): void {
                CreateDiscount::make([
                    'model_type' => $modelType,
                    'model_id' => $toModelId,
                    'name' => $discount->name,
                    'discount' => $discount->getRawOriginal('discount'),
                    'is_percentage' => $discount->is_percentage,
                ])
                    ->validate()
                    ->execute();
            });
    }
}
