<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Rulesets\OrderPosition\UpdateOrderPositionRuleset;
use FluxErp\View\Printing\Order\FinalInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateOrderPosition extends FluxAction
{
    public static function models(): array
    {
        return [OrderPosition::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateOrderPositionRuleset::class;
    }

    public function performAction(): Model
    {
        $tags = Arr::pull($this->data, 'tags', []);

        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->whereKey($this->data['id'] ?? null)
            ->firstOrNew();

        $order = resolve_static(Order::class, 'query')
            ->whereKey(data_get($this->data, 'order_id', $orderPosition->order_id))
            ->select(['id', 'client_id', 'price_list_id'])
            ->first();

        $this->data['client_id'] ??= $order->client_id;
        $this->data['price_list_id'] ??= $order->price_list_id;

        $orderPosition->fill($this->data);

        $product = null;
        if ($orderPosition->isDirty('product_id') && $orderPosition->product_id) {
            $product = resolve_static(Product::class, 'query')
                ->whereKey($this->getData('product_id'))
                ->with([
                    'bundleProducts:id,name',
                ])
                ->first();

            $orderPosition->vat_rate_id = $orderPosition->isDirty('vat_rate_id') ?
                $orderPosition->vat_rate_id : $product->vat_rate_id;
            $orderPosition->name = $orderPosition->isDirty('name') ?
                $orderPosition->name : $product->name;
            $orderPosition->description = $orderPosition->isDirty('description') ?
                $orderPosition->description : $product->description;
            $orderPosition->product_number = $orderPosition->isDirty('product_number') ?
                $orderPosition->product_number : $product->product_number;
            $orderPosition->ean_code = $orderPosition->isDirty('ean_code') ?
                $orderPosition->ean_code : $product->ean_code;
            $orderPosition->unit_gram_weight = $orderPosition->isDirty('unit_gram_weight') ?
                $orderPosition->unit_gram_weight : $product->unit_gram_weight;
        }

        PriceCalculation::make($orderPosition, $this->data)->calculate();
        unset($orderPosition->discounts, $orderPosition->unit_price);
        $orderPosition->save();

        if ($product?->bundlePositions?->isNotEmpty()) {
            $product = $orderPosition->product()->with('bundleProducts')->first();
            $sortNumber = $orderPosition->sort_number;
            $product->bundleProducts
                ->map(function (Product $bundleProduct) use ($orderPosition, &$sortNumber) {
                    $sortNumber++;

                    return [
                        'client_id' => $orderPosition->client_id,
                        'order_id' => $orderPosition->order_id,
                        'parent_id' => $orderPosition->id,
                        'product_id' => $bundleProduct->id,
                        'vat_rate_id' => $bundleProduct->vat_rate_id,
                        'warehouse_id' => $orderPosition->warehouse_id,
                        'amount' => bcmul($bundleProduct->pivot->count, $orderPosition->amount),
                        'amount_bundle' => $bundleProduct->pivot->count,
                        'name' => $bundleProduct->name,
                        'product_number' => $bundleProduct->product_number,
                        'sort_number' => $sortNumber,
                        'purchase_price' => 0,
                        'vat_rate_percentage' => 0,
                        'is_net' => $orderPosition->is_net,
                        'is_free_text' => false,
                        'is_bundle_position' => true,
                    ];
                })
                ->each(function (array $bundleProduct): void {
                    CreateOrderPosition::make($bundleProduct)
                        ->validate()
                        ->execute();
                });
        }

        $orderPosition->syncTags($tags);

        return $orderPosition->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('id')) {
            $errors = [];
            $orderPosition = resolve_static(OrderPosition::class, 'query')
                ->whereKey($this->data['id'])
                ->with([
                    'order:id,contact_id,order_type_id,is_locked',
                    'order.orderType:id,order_type_enum',
                    'origin:id,order_id,amount',
                    'origin.order:id,order_type_id',
                    'origin.order.orderType:id,order_type_enum',
                ])
                ->first();

            if ($orderPosition->order->is_locked) {
                $errors += [
                    'is_locked' => [__('Order is locked')],
                ];
            }

            // Check if new parent causes a cycle
            if (($this->data['parent_id'] ?? false)
                && Helper::checkCycle(OrderPosition::class, $orderPosition, $this->data['parent_id'])
            ) {
                $errors += [
                    'parent_id' => [__('Cycle detected')],
                ];
            }

            if ($this->data['price_id'] ?? false) {
                // Check if the new price exists in the current price list

                if (resolve_static(Price::class, 'query')
                    ->whereKey($this->data['price_id'])
                    ->where(
                        'price_list_id',
                        '!=',
                        $this->data['price_list_id'] ?? $orderPosition->price_list_id
                    )
                    ->exists()
                ) {
                    $errors += [
                        'price_id' => [__('Price not found in price list')],
                    ];
                }
            }

            if ($this->getData('credit_account_id')
                && ! resolve_static(ContactBankConnection::class, 'query')
                    ->whereKey($this->getData('credit_account_id'))
                    ->where('contact_id', $orderPosition->order->contact_id)
                    ->exists()
            ) {
                $errors += [
                    'credit_account_id' => [__('validation.exists', ['attribute' => 'credit_account_id'])],
                ];
            }

            // If order position has origin_position_id or is their parent, validate amount
            if (! data_get($this->data, 'is_free_text', $orderPosition->is_free_text)) {
                if (
                    $orderPosition->origin_position_id
                    && ! $orderPosition->origin->order->orderType->order_type_enum->isSubscription()
                ) {
                    $maxAmount = bcsub(
                        $orderPosition->origin->amount,
                        $orderPosition->siblings()
                            ->whereKeyNot($orderPosition->id)
                            ->leftJoin('order_positions AS descendants', function (JoinClause $join): void {
                                $join->on('order_positions.id', '=', 'descendants.origin_position_id')
                                    ->whereNull('descendants.deleted_at');
                            })
                            ->selectRaw(
                                'order_positions.id'
                                . ', order_positions.amount'
                                . ' - SUM(COALESCE(descendants.amount, 0)) AS siblingAmount'
                            )
                            ->groupBy('order_positions.id')
                            ->value('siblingAmount')
                    );

                    if (bccomp(data_get($this->data, 'amount', $orderPosition->amount), $maxAmount) === 1) {
                        throw ValidationException::withMessages([
                            'amount' => [
                                __('validation.max.numeric', ['attribute' => __('amount'), 'max' => $maxAmount]),
                            ],
                        ])->errorBag('updateOrderPosition');
                    }
                }

                if (
                    $orderPosition->descendants()->exists()
                    && ! $orderPosition->order->orderType->order_type_enum->isSubscription()
                    && ! in_array(
                        resolve_static(FinalInvoice::class, 'class'),
                        $orderPosition->order->getPrintViews(),
                        true
                    )
                ) {
                    $minAmount = $orderPosition->descendants()->sum('amount');

                    if (bccomp($minAmount, data_get($this->data, 'amount', $orderPosition->amount)) > 0) {
                        throw ValidationException::withMessages([
                            'amount' => [
                                __('validation.min.numeric', ['attribute' => __('amount'), 'min' => $minAmount]),
                            ],
                        ])->errorBag('updateOrderPosition');
                    }
                }
            }

            if ($errors) {
                throw ValidationException::withMessages($errors)->errorBag('updateOrderPosition');
            }
        }
    }
}
