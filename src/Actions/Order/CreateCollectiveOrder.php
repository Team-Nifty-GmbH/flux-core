<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\Order\CreateCollectiveOrderRuleset;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateCollectiveOrder extends DispatchableFluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCollectiveOrderRuleset::class;
    }

    public function performAction(): array
    {
        $collectiveOrderOrderTypeId = $this->getData('order_type_id');
        $splitOrderOrderTypeId = $this->getData('split_order_order_type_id');

        $success = [];
        $failed = [];

        foreach ($this->getData('orders') as $order) {
            try {
                $collectiveOrder = CreateOrder::make([
                    'address_invoice_id' => data_get($order, 'address_invoice_id'),
                    'order_type_id' => $collectiveOrderOrderTypeId,
                    'tenant_id' => resolve_static(Order::class, 'query')
                        ->whereKey(data_get($order, 'orders.*.id'))
                        ->value('tenant_id'),
                ])
                    ->validate()
                    ->execute();

                // Add all positions with the actual open amount from all the given orders.
                foreach (data_get($order, 'orders') as $childOrder) {
                    $childOrder = resolve_static(Order::class, 'query')
                        ->whereKey(data_get($childOrder, 'id'))
                        ->where('address_invoice_id', $collectiveOrder->address_invoice_id)
                        ->with([
                            'orderPositions' => fn (HasMany $query) => $query->ordered(),
                            'orderType:id,name',
                        ])
                        ->select([
                            'id',
                            'order_type_id',
                            'order_number',
                            'total_base_discounted_net_price',
                            'total_net_price',
                        ])
                        ->firstOrFail();

                    // Lock the given orders, so that they cannot be modified later on.
                    ToggleLock::make([
                        'id' => $childOrder->getKey(),
                        'is_locked' => true,
                    ])
                        ->validate()
                        ->execute();

                    // Group all positions into a block per order
                    $blockPosition = CreateOrderPosition::make([
                        'order_id' => $collectiveOrder->getKey(),
                        'name' => $childOrder->orderType->name . ' ' . $childOrder->order_number,
                        'is_free_text' => true,
                    ])
                        ->validate()
                        ->execute();

                    $collectiveOrderPositions = collect();
                    foreach ($childOrder->orderPositions as $orderPosition) {
                        $position = $orderPosition->toArray();
                        $position['created_from_id'] = $orderPosition->getKey();
                        $position['order_id'] = $collectiveOrder->getKey();

                        if (data_get($orderPosition, 'parent_id')) {
                            $position['parent_id'] = $collectiveOrderPositions
                                ->firstWhere('created_from_id', $orderPosition->parent_id)
                                ?->getKey() ?? $blockPosition->getKey();
                        } else {
                            $position['parent_id'] = $blockPosition->getKey();
                        }

                        // Calculate discount_percentage by also taking the order discount into account
                        $discountPercentage = bcsub(
                            1,
                            bcmul(
                                bcsub(1, data_get($orderPosition, 'discount_percentage') ?? 0),
                                bcsub(1, $childOrder->discountPercentage)
                            )
                        );
                        $collectiveOrderPosition = CreateOrderPosition::make(
                            array_merge(
                                array_diff_key(
                                    $position,
                                    array_flip([
                                        'id',
                                        'uuid',
                                        'sort_number',
                                        'amount_packed_products',
                                        'total_net_price',
                                        'total_gross_price',
                                    ])
                                ),
                                [
                                    'discount_percentage' => $discountPercentage,
                                    'unit_price' => $orderPosition->unitPrice,
                                ]
                            )
                        )
                            ->validate()
                            ->execute();

                        $collectiveOrderPositions->push($collectiveOrderPosition);

                        // Set the origin position id of all order positions to the corresponding id
                        // of the collective order's positions
                        $orderPosition->origin_position_id = $collectiveOrderPosition->getKey();
                        $orderPosition->save();
                    }

                    $collectiveOrder->calculatePrices()->save();

                    // Make all given orders children of the new collective order and change their order types
                    $childOrder->order_type_id = $splitOrderOrderTypeId;
                    $childOrder->parent_id = $collectiveOrder->getKey();
                    $childOrder->balance = 0;
                    $childOrder->save();
                }
            } catch (Throwable $e) {
                report($e);
                $failed[] = [
                    'address_invoice_id' => data_get($order, 'address_invoice_id'),
                    'error' => $e->getMessage(),
                ];

                continue;
            }

            $success[] = [
                'address_invoice_id' => data_get($order, 'address_invoice_id'),
                'collective_order_id' => $collectiveOrder->getKey(),
            ];
        }

        return [
            'success' => $success,
            'failed' => $failed,
        ];
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Get tenant ids from all selected orders
        $tenantIds = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('orders.*.orders.*.id'))
            ->pluck('tenant_id')
            ->toArray();

        // Check if given order types are assigned to ALL selected tenant ids
        $errors = [];
        $orderTypes = [
            'order_type_id',
            'split_order_order_type_id',
        ];
        foreach ($orderTypes as $orderType) {
            $orderTypeTenantIds = resolve_static(OrderType::class, 'query')
                ->whereKey($this->getData($orderType))
                ->first(['id'])
                ?->getTenants(['id'])
                ->pluck('id')
                ->toArray();

            if (array_diff($tenantIds, $orderTypeTenantIds)) {
                $errors += [
                    $orderType => ['Given order type is not available for all selected orders'],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createCollectiveOrder');
        }
    }
}
