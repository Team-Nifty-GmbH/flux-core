<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\OrderPosition\CreateOrderPositionRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateOrderPosition extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateOrderPositionRuleset::class;
    }

    public function setRulesFromRulesets(): static
    {
        return parent::setRulesFromRulesets()
            ->mergeRules([
                'vat_rate_percentage' => [
                    Rule::excludeIf(
                        data_get($this->data, 'is_free_text', false)
                        || data_get($this->data, 'is_bundle_position', false)
                        || data_get($this->data, 'vat_rate_id', false)
                    ),
                    Rule::requiredIf(
                        ! data_get($this->data, 'is_free_text', false)
                        && ! data_get($this->data, 'is_bundle_position', false)
                        && ! data_get($this->data, 'vat_rate_id', false)
                        && ! data_get($this->data, 'product_id', false)
                    ),
                    app(Numeric::class),
                ],
            ]);
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): OrderPosition
    {
        $tags = Arr::pull($this->data, 'tags', []);
        $order = resolve_static(Order::class, 'query')
            ->with(['orderType:id,order_type_enum', 'priceList:id,is_net'])
            ->whereKey($this->data['order_id'])
            ->first();
        $orderPosition = app(OrderPosition::class);

        $this->data['is_net'] ??= data_get($order, 'priceList.is_net', false);
        $this->data['client_id'] ??= data_get(
            $order,
            'client_id',
            Client::default()
        );
        $this->data['price_list_id'] ??= data_get(
            $order,
            'price_list_id',
            PriceList::default()
        );

        if (is_int($this->data['sort_number'] ?? false)) {
            $currentHighestSortNumber = resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->data['order_id'])
                ->max('sort_number');
            $this->data['sort_number'] = min($this->data['sort_number'], $currentHighestSortNumber + 1);

            $orderPosition->sortable['sort_when_creating'] = false;
            resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->data['order_id'])
                ->where('sort_number', '>=', $this->data['sort_number'])
                ->increment('sort_number');
        }

        if ($order->orderType->order_type_enum->isPurchase() && is_null(data_get($this->data, 'ledger_account_id'))) {
            $this->data['ledger_account_id'] = $order->contact->expense_ledger_account_id;
        }

        $product = null;
        if (data_get($this->data, 'product_id', false)) {
            $product = resolve_static(Product::class, 'query')
                ->whereKey($this->data['product_id'])
                ->with([
                    'bundleProducts:id,name',
                ])
                ->first();

            data_set($this->data, 'vat_rate_id', $product->vat_rate_id, false);
            data_set($this->data, 'name', $product->name, false);
            data_set($this->data, 'description', $product->description, false);
            data_set($this->data, 'product_number', $product->product_number, false);
            data_set($this->data, 'ean_code', $product->ean, false);
            data_set($this->data, 'unit_gram_weight', $product->weight_gram, false);

            if (! ($this->data['warehouse_id'] ?? false)) {
                $this->data['warehouse_id'] = Warehouse::default()?->id;
            }
        }

        if (! ($this->data['is_free_text'] ?? false)) {
            $this->data['amount'] = $this->data['amount'] ?? 1;
        }

        $orderPosition->fill($this->data);

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

        $orderPosition->attachTags($tags);

        return $orderPosition->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(OrderPosition::class));

        $this->data = $validator->validate();

        // Only allow creation of order_position if exists in parent order and amount not greater than totalAmount
        if (! ($this->data['is_free_text'] ?? false)) {
            $order = resolve_static(Order::class, 'query')
                ->whereKey($this->data['order_id'])
                ->first();

            if ($order?->parent_id
                && in_array($order->orderType->order_type_enum, [OrderTypeEnum::Retoure, OrderTypeEnum::SplitOrder])
            ) {
                if (! $originPositionId = data_get($this->data, 'origin_position_id')) {
                    throw ValidationException::withMessages([
                        'origin_position_id' => [__('validation.required', ['attribute' => 'origin_position_id'])],
                    ])->errorBag('createOrderPosition');
                }

                if (! resolve_static(OrderPosition::class, 'query')
                    ->whereKey($originPositionId)
                    ->where('order_id', $order->parent_id)
                    ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'origin_position_id' => ['Order position does not exists in parent order.'],
                    ]);
                }

                $originPosition = resolve_static(OrderPosition::class, 'query')
                    ->whereKey($originPositionId)
                    ->withSum('descendants as descendantsAmount', 'amount')
                    ->first();
                $maxAmount = bcsub(
                    $originPosition->amount,
                    $originPosition->descendantsAmount ?? 0,
                );

                if (bccomp($this->data['amount'] ?? 1, $maxAmount) > 0) {
                    throw ValidationException::withMessages([
                        'amount' => [__('validation.max.numeric', ['attribute' => __('amount'), 'max' => $maxAmount])],
                    ])->errorBag('createOrderPosition');
                }
            }
        }
    }
}
