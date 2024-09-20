<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\StockPosting\CreateStockPostingsFromOrderRuleset;
use Illuminate\Validation\ValidationException;

class CreateStockPostingsFromOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateStockPostingsFromOrderRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): bool
    {
        $order = resolve_static(Order::class, 'query')
            ->where('id', $this->data['id'])
            ->with([
                'orderPositions' => fn ($query) => $query->where('is_free_text', false)
                    ->select(['id', 'order_id', 'product_id', 'warehouse_id', 'amount']),
                'orderPositions.reservedStock',
                'orderPositions.stockPostings',
                'orderPositions.product:id,name,is_nos',
                'orderType:id,order_type_enum',
            ])
            ->first(['id', 'order_type_id', 'order_number']);

        $postStock = ! data_get($this->data, 'only_reserve_stock', false);

        $multiplier = $order->orderType->order_type_enum->multiplier();
        foreach ($order->orderPositions as $orderPosition) {
            $posting = bcabs($orderPosition->stockPostings()->sum('posting'));

            if (bccomp($posting, $orderPosition->amount) >= 0) {
                continue;
            }

            $orderPosition->warehouse_id ??= resolve_static(Warehouse::class, 'default')?->id;
            $open = bcsub($orderPosition->amount, $posting);

            // Handle Purchase Orders and alike.
            if ($multiplier === -1 && $postStock) {
                CreateStockPosting::make([
                    'warehouse_id' => $orderPosition->warehouse_id,
                    'product_id' => $orderPosition->product_id,
                    'order_position_id' => $orderPosition->id,
                    'purchase_price' => $orderPosition->unit_net_price,
                    'posting' => $open,
                    'description' => $order->order_number,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                continue;
            } elseif ($multiplier === -1) {
                continue;
            }

            // Handle Sales Orders and alike.
            $reserved = $orderPosition->reservedStock()->sum('reserved_amount');

            // Check if stock is already reserved.
            if (bccomp($open, $reserved) <= 0) {
                if ($postStock) {
                    $this->postReservedStock($orderPosition, $open, $order->order_number);
                }

                continue;
            }

            $availableStock = resolve_static(StockPosting::class, 'query')
                ->where('product_id', $orderPosition->product_id)
                ->where('warehouse_id', $orderPosition->warehouse_id)
                ->sum('remaining_stock');

            if (bccomp($open, bcadd($availableStock, $reserved)) === 1 && ! $orderPosition->product->is_nos) {
                throw ValidationException::withMessages([
                    'orderPositions' => [
                        __(
                            'Not enough stock available in warehouse :warehouse for product :product.',
                            [
                                'warehouse' => $orderPosition->warehouse?->name,
                                'product' => $orderPosition->product->name,
                            ]
                        ),
                    ],
                ]);
            }

            $stockPostings = resolve_static(StockPosting::class, 'query')
                ->where('product_id', $orderPosition->product_id)
                ->where('warehouse_id', $orderPosition->warehouse_id)
                ->where('remaining_stock', '>', 0)
                ->get(['id', 'remaining_stock', 'reserved_stock', 'purchase_price']);

            if ($postStock) {
                // Post reserved stock
                $this->postReservedStock($orderPosition, $open, $order->order_number);

                // Post remaining stock
                $open = bcsub($open, $reserved);
                foreach ($stockPostings as $stockPosting) {
                    if (bccomp($open, 0) <= 0) {
                        break;
                    }

                    $posting = bccomp($open, $stockPosting->remaining_stock) === 1
                        ? $stockPosting->remaining_stock
                        : $open;

                    CreateStockPosting::make([
                        'warehouse_id' => $orderPosition->warehouse_id,
                        'product_id' => $orderPosition->product_id,
                        'parent_id' => $stockPosting->id,
                        'order_position_id' => $orderPosition->id,
                        'serial_number_id' => $stockPosting->serial_number_id,
                        'posting' => bcmul($posting, -1),
                        'purchase_price' => $stockPosting->purchase_price,
                        'description' => $order->order_number,
                    ])
                        ->checkPermission()
                        ->validate()
                        ->execute();

                    UpdateStockPosting::make([
                        'id' => $stockPosting->id,
                        'remaining_stock' => bcsub($stockPosting->remaining_stock, $posting),
                    ])
                        ->checkPermission()
                        ->validate()
                        ->execute();

                    $open = bcsub($open, $posting);
                }

                if (bccomp($open, 0) === 1 && $orderPosition->product->is_nos) {
                    CreateStockPosting::make([
                        'warehouse_id' => $orderPosition->warehouse_id,
                        'product_id' => $orderPosition->product_id,
                        'order_position_id' => $orderPosition->id,
                        'posting' => bcmul($open, -1),
                        'description' => $order->order_number,
                    ])
                        ->checkPermission()
                        ->validate()
                        ->execute();
                }
            } else {
                $open = bcsub($open, $reserved);
                // Reserve remaining stock
                foreach ($stockPostings as $stockPosting) {
                    if (bccomp($open, 0) === 0) {
                        break;
                    }

                    $posting = bccomp($open, $stockPosting->remaining_stock) === 1
                        ? $stockPosting->remaining_stock
                        : $open;

                    $orderPosition->reservedStock()->attach($stockPosting->id, ['reserved_amount' => $posting]);

                    UpdateStockPosting::make([
                        'id' => $stockPosting->id,
                        'remaining_stock' => bcsub($stockPosting->remaining_stock, $posting),
                        'reserved_stock' => bcadd($stockPosting->reserved_stock, $posting),
                    ])
                        ->checkPermission()
                        ->validate()
                        ->execute();

                    $open = bcsub($open, $posting);
                }
            }
        }

        return true;
    }

    protected function postReservedStock(
        OrderPosition $orderPosition,
        string|int|float $open,
        ?string $orderNumber = null): void
    {
        // Used reserved stock for stock posting.
        foreach ($orderPosition->reservedStock as $stockPosting) {
            if (bccomp($open, 0) === 0) {
                UpdateStockPosting::make([
                    'id' => $stockPosting->id,
                    'remaining_stock' => bcadd(
                        $stockPosting->remaining_stock,
                        $stockPosting->pivot->reserved_amount
                    ),
                    'reserved_stock' => bcsub(
                        $stockPosting->reserved_stock,
                        $stockPosting->pivot->reserved_amount
                    ),
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $orderPosition->reservedStock()->detach();
            }

            if (bccomp($open, $stockPosting->pivot->reserved_amount) === -1) {
                $posting = $open;

                $open = 0;
            } else {
                $posting = $stockPosting->pivot->reserved_amount;

                $open = bcsub($open, $posting);
            }

            CreateStockPosting::make([
                'warehouse_id' => $orderPosition->warehouse_id,
                'product_id' => $orderPosition->product_id,
                'order_position_id' => $orderPosition->id,
                'serial_number_id' => $stockPosting->serial_number_id,
                'posting' => bcmul($posting, -1),
                'purchase_price' => $stockPosting->purchase_price,
                'description' => $orderNumber,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            UpdateStockPosting::make([
                'id' => $stockPosting->id,
                'remaining_stock' => bccomp($stockPosting->pivot->reserved_amount, $posting) === 1
                    ? bcadd(
                        $stockPosting->remaining_stock,
                        bcsub($stockPosting->pivot->reserved_amount, $posting)
                    )
                    : $stockPosting->remaining_stock,
                'reserved_stock' => bcsub(
                    $stockPosting->reserved_stock,
                    $stockPosting->pivot->reserved_amount
                ),
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            $orderPosition->reservedStock()->detach();
        }
    }
}
