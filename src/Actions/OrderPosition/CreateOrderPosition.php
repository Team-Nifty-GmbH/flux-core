<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateOrderPosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = array_merge(
            (new CreateOrderPositionRequest())->rules(),
            [
                'price_list_id' => [
                    Rule::requiredIf(
                        ! data_get($this->data, 'is_free_text', false)
                        && ! data_get($this->data, 'is_bundle_position', false)
                        && ! data_get($this->data, 'price_id', false)
                    ),
                    'integer',
                    'exists:price_lists,id,deleted_at,NULL',
                    'exclude_if:is_free_text,true',
                ],
                'purchase_price' => [
                    Rule::requiredIf(
                        ! data_get($this->data, 'is_free_text', false)
                        && ! data_get($this->data, 'is_bundle_position', false)
                        && data_get($this->data, 'product_id', false)
                        && ! data_get($this->data, 'price_id', false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
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
                    ),
                    'numeric',
                ],
            ]
        );
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): OrderPosition
    {
        $this->data['amount'] = $this->data['amount'] ?? 1;

        $tags = Arr::pull($this->data, 'tags', []);
        $order = Order::query()
            ->with('orderType:id,order_type_enum')
            ->whereKey($this->data['order_id'])
            ->first();
        $orderPosition = new OrderPosition();

        $this->data['client_id'] = data_get($this->data, 'client_id', $order->client_id);
        $this->data['price_list_id'] = data_get($this->data, 'price_list_id', $order->price_list_id);

        if (is_int($this->data['sort_number'] ?? false)) {
            $currentHighestSortNumber = OrderPosition::query()
                ->where('order_id', $this->data['order_id'])
                ->max('sort_number');
            $this->data['sort_number'] = min($this->data['sort_number'], $currentHighestSortNumber + 1);

            $orderPosition->sortable['sort_when_creating'] = false;
            OrderPosition::query()->where('order_id', $this->data['order_id'])
                ->where('sort_number', '>=', $this->data['sort_number'])
                ->increment('sort_number');
        }

        if ($order->orderType->order_type_enum->isPurchase() && ($this->data['ledger_account_id'] ?? false)) {
            $this->data['ledger_account_id'] = $order->contact->expense_ledger_account_id;
        }

        $product = null;
        if (data_get($this->data, 'product_id', false)) {
            $product = Product::query()
                ->with([
                    'bundleProducts:id,name',
                ])
                ->whereKey($this->data['product_id'])
                ->first();

            data_set($this->data, 'vat_rate_id', $product->vat_rate_id, false);
            data_set($this->data, 'name', $product->name, false);
            data_set($this->data, 'description', $product->description, false);
            data_set($this->data, 'product_number', $product->product_number, false);
            data_set($this->data, 'ean_code', $product->ean, false);
            data_set($this->data, 'unit_gram_weight', $product->weight_gram, false);
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
                    try {
                        CreateOrderPosition::make($bundleProduct)
                            ->validate()
                            ->execute();
                    } catch (ValidationException) {
                    }
                });
        }

        $orderPosition->attachTags($tags);

        return $orderPosition->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new OrderPosition());

        $this->data = $validator->validate();
    }
}
