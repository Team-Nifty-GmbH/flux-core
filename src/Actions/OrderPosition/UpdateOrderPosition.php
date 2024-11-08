<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Rulesets\OrderPosition\UpdateOrderPositionRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateOrderPosition extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateOrderPositionRuleset::class;
    }

    public static function models(): array
    {
        return [OrderPosition::class];
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

        $this->data['client_id'] = data_get($this->data, 'client_id', $order->client_id);
        $this->data['price_list_id'] = data_get($this->data, 'price_list_id', $order->price_list_id);

        if (is_int($this->data['sort_number'] ?? false)
            && $orderPosition->sort_number !== $this->data['sort_number']
        ) {
            $currentHighestSortNumber = resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->data['order_id'])
                ->max('sort_number');
            $this->data['sort_number'] = min($this->data['sort_number'], $currentHighestSortNumber + 1);

            resolve_static(OrderPosition::class, 'query')->where('order_id', $this->data['order_id'])
                ->where('sort_number', '>=', $this->data['sort_number'])
                ->increment('sort_number');
        }

        $orderPosition->fill($this->data);

        $product = null;
        if ($orderPosition->isDirty('product_id') && $orderPosition->product_id) {
            $product = resolve_static(Product::class, 'query')
                ->whereKey($this->data['product_id'])
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

        PriceCalculation::fill($orderPosition, $this->data);
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
                ->each(function (array $bundleProduct) {
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
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(OrderPosition::class));

        $this->data = $validator->validate();

        if ($this->data['id'] ?? false) {
            $errors = [];
            $orderPosition = resolve_static(OrderPosition::class, 'query')
                ->whereKey($this->data['id'])
                ->with('order:id,is_locked')
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

            // If order position has origin_position_id or is their parent, validate amount
            if (! data_get($this->data, 'is_free_text', $orderPosition->is_free_text)) {
                if ($orderPosition->origin_position_id) {
                    $maxAmount = bcsub(
                        $orderPosition->origin->amount,
                        $orderPosition->siblings()
                            ->where('id', '!=', $orderPosition->id)
                            ->sum('amount')
                    );

                    if (bccomp(data_get($this->data, 'amount', $orderPosition->amount), $maxAmount) > 0) {
                        throw ValidationException::withMessages([
                            'amount' => [
                                __('validation.max.numeric', ['attribute' => __('amount'), 'max' => $maxAmount]),
                            ],
                        ])->errorBag('updateOrderPosition');
                    }
                }

                if ($orderPosition->descendants()->exists()) {
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
