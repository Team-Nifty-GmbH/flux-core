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
                'price_id' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false &&
                        (($this->data['product_id'] ?? false) && (! ($this->data['price_list_id'] ?? false)))
                    ),
                    'integer',
                    'nullable',
                    'exists:prices,id,deleted_at,NULL',
                    'exclude_if:is_free_text,true',
                ],
                'price_list_id' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ! ($this->data['price_id'] ?? false)
                    ),
                    'integer',
                    'exists:price_lists,id,deleted_at,NULL',
                    'exclude_if:is_free_text,true',
                ],
                'purchase_price' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['product_id'] ?? false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
                'unit_price' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['price_id'] ?? false)
                    ),
                    'numeric',
                    'exclude_if:is_free_text,true',
                ],
                'vat_rate_percentage' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ! ($this->data['vat_rate_id'] ?? false)
                    ),
                    Rule::excludeIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['vat_rate_id'] ?? false)
                    ),
                    'numeric',
                ],
                'product_number' => [
                    Rule::requiredIf(
                        ($this->data['is_free_text'] ?? false) === false && ($this->data['product_id'] ?? false)
                    ),
                    'string',
                    'nullable',
                    'exclude_if:is_free_text,true',
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
        $tags = Arr::pull($this->data, 'tags', []);
        $order = Order::query()
            ->with('orderType:id,order_type_enum')
            ->whereKey($this->data['order_id'])
            ->first();
        $orderPosition = new OrderPosition();

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

        $orderPosition->fill($this->data);

        PriceCalculation::fill($orderPosition, $this->data);
        unset($orderPosition->discounts);

        $orderPosition->save();

        if ($this->data['product_id'] ?? false) {
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
