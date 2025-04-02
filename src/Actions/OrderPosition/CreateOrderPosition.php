<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\BundleTypeEnum;
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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateOrderPosition extends FluxAction
{
    public static function models(): array
    {
        return [OrderPosition::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateOrderPositionRuleset::class;
    }

    public function performAction(): OrderPosition
    {
        $tags = Arr::pull($this->data, 'tags', []);
        $order = resolve_static(Order::class, 'query')
            ->with(['orderType:id,order_type_enum', 'priceList:id,is_net'])
            ->whereKey($this->getData('order_id'))
            ->first();
        $orderPosition = app(OrderPosition::class);

        $this->data['is_net'] ??= $this->getData('priceList.is_net', false);
        $this->data['client_id'] ??= $this->getData('client_id') ?? Client::default()?->getKey();
        $this->data['price_list_id'] ??= $this->getData('price_list_id') ?? PriceList::default()?->getKey();

        if (is_int($this->getData('sort_number'))) {
            $currentHighestSortNumber = resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->getData('order_id'))
                ->max('sort_number');
            $this->data['sort_number'] = min($this->getData('sort_number'), $currentHighestSortNumber + 1);

            $orderPosition->sortable['sort_when_creating'] = false;
            resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->getData('order_id'))
                ->where('sort_number', '>=', $this->getData('sort_number'))
                ->increment('sort_number');
        }

        if ($order->orderType->order_type_enum->isPurchase() && is_null($this->getData('ledger_account_id'))) {
            $this->data['ledger_account_id'] = $order->contact->expense_ledger_account_id;
        }

        $product = null;
        if ($this->getData('product_id')) {
            $product = resolve_static(Product::class, 'query')
                ->whereKey($this->getData('product_id'))
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
            $this->data['warehouse_id'] ??= Warehouse::default()?->getKey();

            if ($product->bundle_type_enum === BundleTypeEnum::Group) {
                $this->data['is_free_text'] = true;
            }
        }

        if (! is_numeric($this->getData('amount')) && ! $this->getData('is_free_text')) {
            $this->data['amount'] = 1;
        }

        $orderPosition->fill($this->data);

        if (! $this->getData('is_free_text')) {
            PriceCalculation::fill($orderPosition, $this->data);
        }
        unset($orderPosition->discounts, $orderPosition->unit_price);

        $orderPosition->save();

        if ($product?->bundleProducts?->isNotEmpty()) {
            $product = $orderPosition->product()->with('bundleProducts')->first();
            $product->bundleProducts
                ->map(function (Product $bundleProduct) use ($orderPosition) {
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
                        'purchase_price' => 0,
                        'vat_rate_percentage' => 0,
                        'is_net' => $orderPosition->is_net ?? false,
                        'is_free_text' => false,
                        'is_bundle_position' => ! $this->data['is_free_text'],
                    ];
                })
                ->each(function (array $bundleProduct): void {
                    CreateOrderPosition::make($bundleProduct)
                        ->validate()
                        ->execute();
                });
        }

        $orderPosition->attachTags($tags);

        return $orderPosition->withoutRelations()->fresh();
    }

    public function setRulesFromRulesets(): static
    {
        return parent::setRulesFromRulesets()
            ->mergeRules([
                'vat_rate_percentage' => [
                    Rule::excludeIf(
                        $this->getData('is_free_text', false)
                        || $this->getData('is_bundle_position', false)
                        || $this->getData('vat_rate_id', false)
                    ),
                    Rule::requiredIf(
                        ! $this->getData('is_free_text', false)
                        && ! $this->getData('is_bundle_position', false)
                        && ! $this->getData('vat_rate_id', false)
                        && ! $this->getData('product_id', false)
                    ),
                    app(Numeric::class),
                ],
            ]);
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('order_id'))
            ->first();

        if ($order->is_locked) {
            $errors += [
                'is_locked' => [__('Order is locked')],
            ];
        }

        // Only allow creation of order_position if exists in parent order and amount not greater than totalAmount
        if (! $this->getData('is_free_text')) {
            if ($order?->parent_id
                && in_array($order->orderType->order_type_enum, [OrderTypeEnum::Retoure, OrderTypeEnum::SplitOrder])
            ) {
                if (! $originPositionId = $this->getData('origin_position_id')) {
                    $errors += [
                        'origin_position_id' => [__('validation.required', ['attribute' => 'origin_position_id'])],
                    ];
                }

                if (! resolve_static(OrderPosition::class, 'query')
                    ->whereKey($originPositionId)
                    ->where('order_id', $order->parent_id)
                    ->exists()
                ) {
                    $errors += [
                        'origin_position_id' => [__('Order position does not exists in parent order.')],
                    ];
                }

                $originPosition = resolve_static(OrderPosition::class, 'query')
                    ->whereKey($originPositionId)
                    ->withSum('descendants as descendantsAmount', 'amount')
                    ->first();
                $maxAmount = bcsub(
                    $originPosition->amount,
                    $originPosition->descendantsAmount ?? 0,
                );

                if (bccomp($this->getData('amount', 1), $maxAmount) > 0) {
                    $errors += [
                        'amount' => [__('validation.max.numeric', ['attribute' => __('amount'), 'max' => $maxAmount])],
                    ];
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('createOrderPosition');
        }
    }
}
