<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\CreateOrderPositionRequest;
use FluxErp\Http\Requests\UpdateOrderPositionRequest;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use FluxErp\Rules\Numeric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderPositionService
{
    public function create(array $data): OrderPosition
    {
        $orderPosition = new OrderPosition($data);

        $this->fillPriceCalculation(
            $orderPosition,
            $data
        );

        unset($orderPosition->tags);
        unset($orderPosition->discounts);

        $orderPosition->save();
        $orderPosition->attachTags($data['tags'] ?? []);

        return $orderPosition;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateOrderPositionRequest(),
            service: $this
        );

        foreach ($data as $item) {
            if ($item['id'] ?? false) {
                $orderPosition = OrderPosition::query()
                    ->whereKey($item['id'])
                    ->first();
            } else {
                $orderPosition = new OrderPosition();
            }

            $orderPosition->fill($item);

            $this->fillPriceCalculation(
                $orderPosition,
                $item
            );

            unset($orderPosition->tags);
            unset($orderPosition->discounts);

            $orderPosition->save();
            $orderPosition->syncTags($data['tags'] ?? []);

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $orderPosition->withoutRelations()->fresh(),
                additions: ['id' => $orderPosition->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'order-position(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $orderPosition = OrderPosition::query()
            ->whereKey($id)
            ->first();

        if (! $orderPosition) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'order-position not found']
            );
        }

        $orderPosition->children()->delete();

        $orderPosition->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'order-position(s) deleted'
        );
    }

    public function fill(int $orderId, array $data, bool $simulate = false): array
    {
        // Validate Data
        $rules = (new CreateOrderPositionRequest())->rules();
        $rules['order_id'] = 'required|integer|size:' . $orderId;
        $rules['children'] = 'array';
        unset($rules['parent_id']);

        $errors = [];
        $validated = [];
        foreach ($data as $key => $orderPosition) {
            $validatedOrderPosition = $this->validateOrderPosition($orderPosition, $rules);
            $errors[$orderPosition['id'] ?? $key] = $validatedOrderPosition['errors'];
            $validated[$key] = $validatedOrderPosition['validated'];
        }

        if ($errors = array_filter($errors)) {
            return ResponseHelper::createArrayResponse(statusCode: 422, data: $errors);
        }

        // Fill validated order positions
        $orderPositions = [];
        $ids = [];
        foreach ($validated as $item) {
            $filled = $this->fillOrderPosition($item, $simulate);
            $orderPositions[] = $filled['orderPositions'];

            if (! $simulate) {
                $ids = array_merge($ids, $filled['ids']);
            }
        }

        // Delete all order positions that were not updated on given orderId
        if (! $simulate) {
            OrderPosition::query()
                ->where('order_id', $orderId)
                ->whereIntegerNotInRaw('id', $ids)
                ->delete();
        }

        if (! $simulate) {
            $order = Order::query()->whereKey($orderId)->first();
            Event::dispatch('order.calculating-prices', $order);
            $order->calculatePrices()->save();
            Event::dispatch('order.calculated-prices', $order);
        }

        return ResponseHelper::createArrayResponse(statusCode: 200, data: $orderPositions);
    }

    public function validateItem(array $item, array $response): ?array
    {
        if ($item['id'] ?? false) {
            $orderPosition = OrderPosition::query()
                ->whereKey($item['id'])
                ->first();

            // Check if new parent causes a cycle
            if ($item['parent_id'] ?? false) {
                if (Helper::checkCycle(OrderPosition::class, $orderPosition, $item['parent_id'])) {
                    return ResponseHelper::createArrayResponse(
                        statusCode: 409,
                        data: ['parent_id' => 'cycle detected'],
                        additions: $response
                    );
                }
            }

            if ($item['price_id'] ?? false) {
                // Check if the new price exists in the current price-list

                $price_list_id = $item['price_list_id'] ?? $orderPosition->price_list_id ?? null;
                if (! $price_list_id ||
                    ($price_list_id !== Price::query()->whereKey($item['price_id'])->first()->price_list_id ?? false)
                ) {
                    return ResponseHelper::createArrayResponse(
                        statusCode: 409,
                        data: ['price_id' => 'price-id not found in price-list'],
                        additions: $response
                    );
                }
            }
        }

        return null;
    }

    public function fillPriceCalculation(Model $orderPosition, array $data): void
    {
        // Return if no price could be calculated
        $price = $data['unit_price'] ?? null;
        $price = is_null($price) ? $orderPosition->price?->price : $price;
        $price = is_null($price)
            ? Price::query()
                ->where('product_id', $orderPosition->product_id)
                ->where('price_list_id', $orderPosition->price_list_id)
                ->first()
                ?->price
            : $price;

        if (is_null($price)) {
            return;
        }

        // A subproduct, aka part of a bundle does not have its own prices.
        if ($orderPosition->is_bundle_position) {
            $orderPosition->fill([
                'unit_gross_price' => null,
                'unit_net_price' => null,
                'total_gross_price' => null,
                'total_net_price' => null,
                'total_base_gross_price' => null,
                'total_base_net_price' => null,
                'discount_percentage' => null,
                'margin' => null,
                'vat_rate_percentage' => null,
                'vat_rate_id' => null,
            ]);

            return;
        }

        // Collect & set missing data
        $orderPosition->vat_rate_percentage = ($data['vat_rate_percentage'] ?? false)
            ?: VatRate::query()
                ->whereKey($orderPosition->vat_rate_id)
                ->first()
                ?->rate_percentage;

        $product = $orderPosition->product;

        if (! $orderPosition->price && $product) {
            $orderPosition->price_id = Price::query()
                ->where('product_id', $product->id)
                ->where('price_list_id', $orderPosition->price_list_id)
                ->first()
                ?->id;
        }

        if ($product) {
            $orderPosition->product_prices = $product->prices()
                ->get([
                    'id',
                    'price_list_id',
                    'price',
                ]);
        }

        // Calculate net and gross unit prices
        if ($orderPosition->is_net) {
            $orderPosition->unit_net_price = $price;
            $orderPosition->unit_gross_price = net_to_gross($price, $orderPosition->vat_rate_percentage);
        } else {
            $orderPosition->unit_gross_price = $price;
            $orderPosition->unit_net_price = gross_to_net($price, $orderPosition->vat_rate_percentage);
        }

        // calculate net and gross base prices
        $orderPosition->total_base_gross_price = bcmul($orderPosition->unit_gross_price, $orderPosition->amount);
        $orderPosition->total_base_net_price = bcmul($orderPosition->unit_net_price, $orderPosition->amount);
        $orderPosition->total_gross_price = $orderPosition->total_base_gross_price;
        $orderPosition->total_net_price = $orderPosition->total_base_net_price;

        // Purchase-price dependent on stock-bookings.
        if (! $orderPosition->purchase_price) {
            $stockPosting = StockPosting::query()
                ->where('product_id', $orderPosition->product_id)
                ->where('warehouse_id', $orderPosition->warehouse_id)
                ->orderByDesc('id')
                ->first();

            $orderPosition->purchase_price = ! $stockPosting ? 0 :
                bcdiv($stockPosting->purchase_price, $stockPosting->posting);
        }

        $discounts = $data['discounts'] ?? [];
        // Finished collecting, start calculating

        // 1. Calculate sum before tax.
        $preDiscountedPrice = $orderPosition->is_net
            ? $orderPosition->total_base_net_price
            : $orderPosition->total_base_gross_price;

        if ($preDiscountedPrice == 0) {
            $orderPosition->vat_price = 0;

            return;
        }

        // 2. Add any discounts.
        $discountedPrice = $preDiscountedPrice;
        foreach ($discounts as $discount) {
            if ($discount['is_percentage']) {
                $discountedPrice = discount($discountedPrice, $discount['discount']);
            } else {
                $discountedPrice = bcsub($discountedPrice, $discount['discount']);
            }
        }

        if (! $discounts && ($data['discount_percentage'] ?? false)) {
            $discountedPrice = discount($discountedPrice, $data['discount_percentage']);
        }

        $discountedNetPrice = $orderPosition->is_net
            ? $discountedPrice
            : gross_to_net($discountedPrice, $orderPosition->vat_rate_percentage);

        $discountedGrossPrice = $orderPosition->is_net
            ? net_to_gross($discountedPrice, $orderPosition->vat_rate_percentage)
            : $discountedPrice;

        $totalDiscountPercentage = diff_percentage($preDiscountedPrice, $discountedPrice);
        $margin = bcsub($discountedNetPrice, bcmul($orderPosition->purchase_price, $orderPosition->amount));

        $orderPosition->margin = $margin;
        $orderPosition->discount_percentage = $totalDiscountPercentage == 0
            ? null
            : $totalDiscountPercentage;

        $orderPosition->total_net_price = $discountedNetPrice;
        $orderPosition->total_gross_price = $discountedGrossPrice;

        if ($orderPosition->vat_rate_percentage) {
            $orderPosition->vat_price = bcsub($orderPosition->total_gross_price, $orderPosition->total_net_price);
        }
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
            && OrderPosition::query()
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
        if ($validated['is_free_text'] ?? false) {
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
        if (($validated['is_free_text'] ?? null) === false) {
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
                        'exists:prices,price_list_id,product_id,' . ($validated['product_id'] ?? 'NULL')
                        . ',deleted_at,NULL',
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
            && Product::query()
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

    private function fillOrderPosition(array $data, bool $simulate, int $parentId = null): array
    {
        $originalChildren = $data['children'] ?? [];
        unset($data['children']);

        if (is_int($data['id'] ?? false)) {
            $orderPosition = OrderPosition::query()
                ->whereKey($data['id'])
                ->first();

            $orderPosition->fill($data);
        } else {
            $orderPosition = new OrderPosition($data);
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
            $this->fillPriceCalculation($orderPosition, $data);
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
                $position = new OrderPosition([
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
