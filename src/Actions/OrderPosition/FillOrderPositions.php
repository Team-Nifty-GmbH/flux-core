<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\OrderPosition\CreateOrderPositionRuleset;
use FluxErp\Rulesets\OrderPosition\FillOrderPositionsRuleset;
use FluxErp\Rulesets\OrderPosition\UpdateOrderPositionRuleset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FillOrderPositions extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(FillOrderPositionsRuleset::class, 'getRules');
    }

    public static function name(): string
    {
        return 'order-position.fill-multiple';
    }

    public static function models(): array
    {
        return [OrderPosition::class, Order::class];
    }

    public function performAction(): array
    {
        // Fill validated order positions
        $orderPositions = [];
        $ids = [];
        foreach ($this->data['order_positions'] as $item) {
            $filled = $this->fillOrderPosition($item, $this->data['simulate']);
            $orderPositions[] = $filled['orderPositions'];

            if (! $this->data['simulate']) {
                $ids = array_merge($ids, $filled['ids']);
            }
        }

        // Delete all order positions that were not updated on given orderId
        if (! $this->data['simulate']) {
            app(OrderPosition::class)->query()
                ->where('order_id', $this->data['order_id'])
                ->whereIntegerNotInRaw('id', $ids)
                ->delete();
        }

        if (! $this->data['simulate']) {
            $order = app(Order::class)->query()->whereKey($this->data['order_id'])->first();
            Event::dispatch('order.calculating-prices', $order);
            $order->calculatePrices()->save();
            Event::dispatch('order.calculated-prices', $order);
        }

        return $orderPositions;
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Validate Data
        $rules = [
            'children' => 'array',
            'order_id' => 'required|integer|size:' . $this->data['order_id'],
        ];

        $createRules = array_merge(resolve_static(CreateOrderPositionRuleset::class, 'getRules'), $rules);
        $updateRules = array_merge(resolve_static(UpdateOrderPositionRuleset::class, 'getRules'), $rules);
        unset($createRules['parent_id'], $updateRules['parent_id']);

        $errors = [];
        $orderPositions = [];
        foreach ($this->data['order_positions'] as $key => $orderPosition) {
            $validatedOrderPosition = $this->validateOrderPosition(
                $orderPosition,
                ($orderPosition['id'] ?? false) ? $updateRules : $createRules
            );
            $errors[$orderPosition['id'] ?? $key] = $validatedOrderPosition['errors'];
            $orderPositions[$key] = $validatedOrderPosition['validated'];
        }

        if ($errors = array_filter($errors)) {
            throw ValidationException::withMessages($errors)->errorBag('fillOrderPositions');
        }

        $deletedOrderPositions = app(OrderPosition::class)->query()
            ->whereIntegerNotInRaw('order_positions.id', array_column($orderPositions, 'id'))
            ->where('order_positions.order_id', $this->data['order_id'])
            ->whereHas('descendants')
            ->pluck('id')
            ->toArray();

        if ($deletedOrderPositions) {
            throw ValidationException::withMessages([
                'deleted_order_positions' => [
                    __(
                        'Unable to delete order positions with id \':ids\'. They have descendants.',
                        ['ids' => implode(', ', $deletedOrderPositions)]
                    ),
                ],
            ]);
        }

        $this->data['order_positions'] = $orderPositions;
    }

    private function validateOrderPosition(array $orderPosition, array $rules, ?array $parent = null): array
    {
        $errors = [];
        $validator = Validator::make($orderPosition, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
        }

        $validated = array_intersect_key($validator->valid(), $rules);

        // Fill validated id if exists in order position or order respectively
        if (is_int($orderPosition['id'] ?? false)
            && ($validated['order_id'] ?? false)
            && app(OrderPosition::class)->query()
                ->whereKey($orderPosition['id'])
                ->where('order_id', $validated['order_id'])
                ->exists()
        ) {
            $validated['id'] = $orderPosition['id'];
        }

        // On root level there should be parent_id = null
        if (! $parent && ($orderPosition['parent_id'] ?? false)) {
            $errors['parent_id'] = [
                'The parent_id field must be null',
            ];
        }

        //Set parent_id to null if on root level
        if (is_null($parent)) {
            $validated['parent_id'] = null;
        }

        // If is_free_text = true, there should be no price on this order position
        if (($validated['is_free_text'] ?? false) || ($orderPosition['is_bundle_position'] ?? false)) {
            $validated = array_merge(
                $validated,
                [
                    'unit_gross_price' => null,
                    'unit_net_price' => null,
                    'total_gross_price' => null,
                    'total_net_price' => null,
                    'total_base_gross_price' => null,
                    'total_base_net_price' => null,
                    'discount_percentage' => null,
                    'margin' => null,
                    'provision' => null,
                    'vat_price' => null,
                    'vat_rate_percentage' => null,
                ]
            );

            // Only Bundle Positions can have an amount
            if (! ($orderPosition['is_bundle_position'] ?? false)) {
                $validated['amount'] = null;
            }
        }

        // Merge prices in validated array if is_free_text = false
        if (! ($validated['is_free_text'] ?? false) && ! ($orderPosition['is_bundle_position'] ?? false)) {
            $priceFields = [
                'unit_gross_price',
                'unit_net_price',
                'total_gross_price',
                'total_net_price',
                'total_base_gross_price',
                'total_base_net_price',
                'vat_price',
                'vat_rate_percentage',
            ];

            $priceValidator = Validator::make($orderPosition, [
                'unit_gross_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['unit_gross_price'])),
                    new Numeric(),
                ],
                'unit_net_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['unit_net_price'])),
                    new Numeric(),
                ],
                'total_gross_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['total_gross_price'])),
                    new Numeric(),
                ],
                'total_net_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['total_net_price'])),
                    new Numeric(),
                ],
                'total_base_gross_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['total_base_gross_price'])),
                    new Numeric(),
                ],
                'total_base_net_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['total_base_net_price'])),
                    new Numeric(),
                ],
                'discount_percentage' => [
                    new Numeric(0, 1),
                    'nullable',
                ],
                'margin' => [
                    new Numeric(),
                    'nullable',
                ],
                'provision' => [
                    new Numeric(),
                    'nullable',
                ],
                'vat_price' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['vat_price'])),
                    new Numeric(),
                ],
                'vat_rate_percentage' => [
                    'required_with:' . implode(',', array_diff($priceFields, ['vat_rate_percentage'])),
                    new Numeric(0, 1),
                ],
            ]);

            if ($priceValidator->fails()) {
                $errors = array_merge($errors, $priceValidator->errors()->toArray());
            } else { // Validate if price could be calculated
                $priceCalculatorValidator = Validator::make($orderPosition, [
                    'price_id' => [
                        'required_without_all:price_list_id,unit_price',
                        'integer',
                        'nullable',
                        'exists:prices,id,deleted_at,NULL',
                    ],
                    'price_list_id' => [
                        Rule::requiredIf(
                            ($validated['product_id'] ?? false) &&
                            ! (
                                ($orderPosition['price_id'] ?? false) ||
                                ($orderPosition['unit_price'] ?? false)
                            )
                        ),
                        'integer',
                        'nullable',
                        'exists:price_lists,id,deleted_at,NULL',
                    ],
                    'unit_price' => [
                        new Numeric(),
                        'nullable',
                    ],
                ]);

                if ($priceCalculatorValidator->fails()) {
                    $errors = array_merge($errors, $priceCalculatorValidator->errors()->toArray());
                }

                $validated = array_merge(
                    $validated,
                    array_map(fn () => null, $priceValidator->getRules()),
                    array_intersect_key($priceValidator->valid(), $priceValidator->getRules()),
                    array_intersect_key($priceCalculatorValidator->valid(), $priceCalculatorValidator->getRules())
                );
            }
        }

        // Validate parent is_free_text = true if position is_free_text = false, has a price
        if ($parent && ($validated['is_free_text'] ?? null) === false) {
            if (! ($parent['is_free_text'] ?? null)) {
                $errors['is_free_text'] = array_merge($errors['is_free_text'] ?? [], [
                    'The field is_free_text must be true or parent is_free_text must be true',
                ]);
            }
        }

        $bundleChildren = false;
        $children = $validated['children'] ?? [];

        // Check Bundle
        if (($validated['product_id'] ?? false)
            && app(Product::class)->query()
                ->whereKey($validated['product_id'])
                ->where('is_bundle', true)
                ->exists()
        ) {
            if ($bundleErrors = $this->validateBundlePositions($children)) {
                $errors['children'] = $bundleErrors;
            } else {
                $bundleChildren = true;
                $validated['children'] = $children;
            }
        }

        // Validate children
        if ($children && ! array_key_exists('children', $errors) && ! $bundleChildren) {
            unset($validated['children']);
            $newParent = $validated;

            foreach ($children as $key => $child) {
                $validatedChild = $this->validateOrderPosition($child, $rules, $newParent);
                $errors['children'][$key] = $validatedChild['errors'];
                $validated['children'][] = $validatedChild['validated'];
            }

            $errors['children'] = array_filter($errors['children']);
        }

        return [
            'errors' => array_filter($errors),
            'validated' => $validated,
        ];
    }

    private function validateBundlePositions(array $bundlePositions): array
    {
        $errors = [];
        foreach ($bundlePositions as $key => $bundlePosition) {
            // Bundle positions must have a product_id, an amount and no prices
            $validator = Validator::make($bundlePosition, [
                'product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
                'amount' => [
                    'required',
                    new Numeric(),
                ],

                'unit_gross_price' => ['sometimes', Rule::in([null])],
                'unit_net_price' => ['sometimes', Rule::in([null])],
                'total_gross_price' => ['sometimes', Rule::in([null])],
                'total_net_price' => ['sometimes', Rule::in([null])],
                'total_base_gross_price' => ['sometimes', Rule::in([null])],
                'total_base_net_price' => ['sometimes', Rule::in([null])],
                'discount_percentage' => ['sometimes', Rule::in([null])],
                'margin' => ['sometimes', Rule::in([null])],
                'provision' => ['sometimes', Rule::in([null])],
                'vat_price' => ['sometimes', Rule::in([null])],
                'vat_rate_percentage' => ['sometimes', Rule::in([null])],
                'vat_rate_id' => ['sometimes', Rule::in([null])],

                'is_free_text' => 'required|boolean|accepted',
                'is_bundle_position' => 'required|boolean|accepted',
            ]);

            if ($validator->fails()) {
                $errors[$key] = $validator->errors()->toArray();
            }
        }

        return $errors;
    }

    private function fillOrderPosition(array $data, bool $simulate, ?int $parentId = null): array
    {
        $originalChildren = $data['children'] ?? [];
        unset($data['children']);

        if (is_int($data['id'] ?? false)) {
            $orderPosition = app(OrderPosition::class)->query()
                ->whereKey($data['id'])
                ->first();

            $orderPosition->fill($data);
        } else {
            $orderPosition = app(OrderPosition::class, ['attributes' => $data]);
        }

        // If parentId !== null, set parent_id
        if ($parentId) {
            $orderPosition->parent_id = $parentId;
        }

        // Fill product info if not already filled
        if ($orderPosition->product) {
            $orderPosition->ean_code = $orderPosition->ean_code ?: $orderPosition->product->ean;
            $orderPosition->unit_gram_weight = $orderPosition->unit_gram_weight ?:
                $orderPosition->product->weight_gram;
            $orderPosition->product_number = $orderPosition->product_number ?:
                $orderPosition->product->product_number;
        }

        // Fill prices if not already filled
        if (! $orderPosition->is_free_text && is_null($orderPosition->vat_price)) {
            PriceCalculation::fill($orderPosition, $data);
        }

        // If simulate = false, save order position, keep track of saved ids
        if (! $simulate) {
            $orderPosition->save();

            $ids = [$orderPosition->id];
        }

        // Create Children
        $children = Collection::make();
        foreach ($originalChildren as $child) {
            if ($child['is_bundle_position']) {
                $child['amount'] = bcmul($child['amount_bundle'], $orderPosition->amount);
            }

            $filled = $this->fillOrderPosition($child, $simulate, $orderPosition->id);

            $children->push($filled['orderPositions']);
            if (! $simulate) {
                $ids = array_merge($ids, $filled['ids']);
            }
        }

        // Fill bundle positions if not already exists
        if ($orderPosition->product?->is_bundle && ! $originalChildren) {
            foreach ($orderPosition->product->bundleProducts as $index => $bundleProduct) {
                $position = app(OrderPosition::class, [
                    'attributes' => [
                        'client_id' => $orderPosition->client_id,
                        'order_id' => $orderPosition->order_id,
                        'parent_id' => $orderPosition->id,
                        'product_id' => $bundleProduct->id,
                        'amount' => bcmul($bundleProduct->pivot->count, $orderPosition->amount),
                        'ean_code' => $bundleProduct->ean,
                        'unit_gram_weight' => $bundleProduct->weight_gram,
                        'description' => $bundleProduct->description,
                        'name' => $bundleProduct->name,
                        'product_number' => $bundleProduct->product_number,
                        'sort_number' => $orderPosition->sort_number + $index,
                        'is_free_text' => true,
                        'is_bundle_position' => true,
                    ],
                ]);

                // Save bundle position and keep track of saved ids
                if (! $simulate) {
                    $position->save();

                    $ids[] = $position->id;
                }

                $children->push($position->withoutRelations());
            }
        }

        $orderPosition->setRelation('children', $children);

        $loadedRelations = $orderPosition->getRelations();
        $orderPosition = $orderPosition->toArray();

        foreach ($loadedRelations as $key => $loadedRelation) {
            unset($orderPosition[$key]);
        }

        return [
            'orderPositions' => array_merge($orderPosition, ['children' => $children->toArray()]),
            'ids' => $ids ?? [],
        ];
    }
}
