<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\BaseAction;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateProductRequest;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProduct extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductRequest())->rules();
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function performAction(): Model
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);
        $productProperties = Arr::mapWithKeys(
            Arr::pull($this->data, 'product_properties', []),
            fn ($item, $key) => [$item['id'] => $item['value']]
        );
        $bundleProducts = Arr::pull($this->data, 'bundle_products', false);
        $prices = Arr::pull($this->data, 'prices', false);
        $tags = Arr::pull($this->data, 'tags');

        $product = Product::query()
            ->whereKey($this->data['id'])
            ->first();

        $product->fill($this->data);

        if ($product->isDirty('is_bundle') && ! $product->is_bundle) {
            $product->bundleProducts()->detach();
        }

        $product->save();

        if (! is_null($tags)) {
            $product->syncTags($tags);
        }

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

        return $product->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Product());

        $this->data = $validator->validate();

        if ($this->data['parent_id'] ?? false) {
            $product = Product::query()
                ->whereKey($this->data['id'])
                ->first();

            if (Helper::checkCycle(Product::class, $product, $this->data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Cycle detected')],
                ])->errorBag('updateProduct');
            }
        }
    }
}
