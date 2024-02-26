<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Http\Requests\CreateProductRequest;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductRequest())->rules();
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function performAction(): Product
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);
        $productCrossSellings = Arr::pull($this->data, 'product_cross_sellings', []);
        $productProperties = Arr::mapWithKeys(
            Arr::pull($this->data, 'product_properties', []),
            fn ($item, $key) => [$item['id'] => $item['value']]
        );
        $bundleProducts = Arr::pull($this->data, 'bundle_products', false);
        $prices = Arr::pull($this->data, 'prices', []);

        $suppliers = Arr::pull($this->data, 'suppliers', false);
        $tags = Arr::pull($this->data, 'tags', []);

        $product = new Product($this->data);
        $product->save();

        $product->productOptions()->attach($productOptions);
        $product->productProperties()->attach($productProperties);

        if ($suppliers) {
            $product->suppliers()->attach($suppliers);
        }

        if ($tags) {
            $product->attachTags(Tag::query()->whereIntegerInRaw('id', $tags)->get());
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

        if (CreatePrice::canPerformAction(false)) {
            foreach ($prices as $price) {
                $price['product_id'] = $product->id;
                try {
                    CreatePrice::make($price)->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }

        if (CreateProductCrossSelling::canPerformAction(false)) {
            foreach ($productCrossSellings as $productCrossSelling) {
                $productCrossSelling['product_id'] = $product->id;
                try {
                    CreateProductCrossSelling::make($productCrossSelling)->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }

        return $product->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Product());

        $this->data = $validator->validate();
    }

    public function prepareForValidation(): void
    {
        if (! data_get($this->data, 'prices') && data_get($this->data, 'parent_id')) {
            $this->data['prices'] = Price::query()
                ->where('product_id', data_get($this->data, 'parent_id'))
                ->get()
                ->toArray();
        }
    }
}
