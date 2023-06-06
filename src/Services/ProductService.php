<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProductRequest;
use FluxErp\Models\Price;
use FluxErp\Models\Product;

class ProductService
{
    public function create(array $data): Product
    {
        $product = new Product();

        $productOptions = $data['product_options'] ?? [];
        $productProperties = $this->parseProductProperties($data['product_properties'] ?? []);
        $bundleProducts = $data['bundle_products'] ?? false;
        $prices = $data['prices'] ?? false;
        $tags = $data['tags'] ?? [];
        unset($data['product_options'],
            $data['product_properties'],
            $data['bundle_products'],
            $data['prices'],
            $data['tags']
        );

        $product->fill($data);
        $product->save();

        $product->attachTags($tags);

        $product->productOptions()->attach($productOptions);
        $product->productProperties()->attach($productProperties);
        $product->prices()->createMany($data['prices'] ?? []);

        if ($product->is_bundle && $bundleProducts) {
            $product->bundleProducts()
                ->sync(
                    collect($bundleProducts)
                        ->unique('id')
                        ->mapWithKeys(fn ($item) => [$item['id'] => ['count' => $item['count']]])
                        ->toArray()
                );
        }

        if ($prices) {
            $product->prices()->createMany($prices);
        }

        return $product->refresh();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProductRequest(),
            model: new Product()
        );

        foreach ($data as $item) {
            $product = Product::query()
                ->whereKey($item['id'])
                ->first();

            $productOptions = $item['product_options'] ?? [];
            $productProperties = $this->parseProductProperties($item['product_properties'] ?? []);
            $bundleProducts = $item['bundle_products'] ?? false;
            $prices = $item['prices'] ?? false;
            $tags = $item['tags'] ?? [];
            unset($item['product_options'],
                $item['product_properties'],
                $item['bundle_products'],
                $item['prices'],
                $item['tags']
            );

            $product->fill($item);

            if ($product->isDirty('is_bundle') && ! $product->is_bundle) {
                $product->bundleProducts()->detach();
            }

            $product->save();

            $product->syncTags($tags);

            $product->productOptions()->sync($productOptions);
            $product->productProperties()->sync($productProperties);

            if ($prices) {
                $priceCollection = collect($prices)->keyBy('price_list_id');
                $product->prices
                    ?->each(function (Price $price) use ($priceCollection) {
                        if ($priceCollection->has($price->price_list_id)) {
                            $price->update($priceCollection->get($price->price_list_id));
                            $priceCollection->forget($price->price_list_id);
                        } else {
                            $price->delete();
                        }
                    });

                $priceCollection->each(fn ($item) => $product->prices()->create($item));
            }

            if ($product->is_bundle && $bundleProducts) {
                $product->bundleProducts()
                    ->sync(
                        collect($bundleProducts)
                            ->unique('id')
                            ->mapWithKeys(fn ($item) => [$item['id'] => ['count' => $item['count']]])
                            ->toArray()
                    );
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $product->withoutRelations()->fresh(),
                additions: ['id' => $product->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'products updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $product = Product::query()
            ->whereKey($id)
            ->first();

        if (! $product) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'product not found']
            );
        }

        if ($product->children()->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['children' => 'product has children']
            );
        }

        $product->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'product deleted'
        );
    }

    private function parseProductProperties(array $properties): array
    {
        $productProperties = [];
        foreach ($properties as $productProperty) {
            $id = $productProperty['id'];
            unset($productProperty['id']);
            $productProperties[$id] = $productProperty;
        }

        return $productProperties;
    }
}
