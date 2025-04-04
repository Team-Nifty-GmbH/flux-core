<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Facades\ProductType;
use FluxErp\Models\Client;
use FluxErp\Models\Pivots\ClientProduct;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Product\CreateProductRuleset;
use Illuminate\Support\Arr;

class CreateProduct extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductRuleset::class;
    }

    public function performAction(): Product
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);
        $productCrossSellings = Arr::pull($this->data, 'product_cross_sellings', []);
        $productProperties = Arr::mapWithKeys(
            Arr::pull($this->data, 'product_properties', []),
            fn ($item, $key) => [$item['id'] => ['value' => $item['value']]]
        );
        $bundleProducts = Arr::pull($this->data, 'bundle_products', false);
        $prices = Arr::pull($this->data, 'prices', []);
        $clients = Arr::pull($this->data, 'clients', []);

        $suppliers = Arr::pull($this->data, 'suppliers', false);
        $tags = Arr::pull($this->data, 'tags', []);

        $product = app(Product::class, ['attributes' => $this->data]);
        $product->save();

        $product->productOptions()->attach($productOptions);
        $product->productProperties()->attach($productProperties);

        if ($suppliers) {
            $product->suppliers()->attach($suppliers);
        }

        if ($tags) {
            $product->attachTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        if ($clients) {
            $product->clients()->attach($clients);
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

        if (resolve_static(CreatePrice::class, 'canPerformAction', [false])) {
            foreach ($prices as $price) {
                $price['product_id'] = $product->id;
                CreatePrice::make($price)
                    ->validate()
                    ->execute();
            }
        }

        if (resolve_static(CreateProductCrossSelling::class, 'canPerformAction', [false])) {
            foreach ($productCrossSellings as $productCrossSelling) {
                $productCrossSelling['product_id'] = $product->id;
                CreateProductCrossSelling::make($productCrossSelling)
                    ->validate()
                    ->execute();
            }
        }

        return $product->refresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['product_type'] ??= data_get(ProductType::getDefault(), 'type');

        if ($this->getData('is_bundle')) {
            $this->data['bundle_type_enum'] ??= BundleTypeEnum::Standard;
        }

        if (! data_get($this->data, 'prices') && data_get($this->data, 'parent_id')) {
            $this->data['prices'] = resolve_static(Price::class, 'query')
                ->where('product_id', data_get($this->data, 'parent_id'))
                ->get()
                ->toArray();
        }

        if (! data_get($this->data, 'clients')) {
            if (data_get($this->data, 'parent_id')) {
                $this->data['clients'] = resolve_static(ClientProduct::class, 'query')
                    ->where('product_id', data_get($this->data, 'parent_id'))
                    ->pluck('client_id')
                    ->toArray();
            } else {
                $this->data['clients'] = [
                    resolve_static(Client::class, 'default')?->id,
                ];
            }
        }

        if (! data_get($this->data, 'vat_rate_id')) {
            if (data_get($this->data, 'parent_id')) {
                $this->data['vat_rate_id'] = resolve_static(Product::class, 'query')
                    ->whereKey(data_get($this->data, 'parent_id'))
                    ->value('vat_rate_id');
            } else {
                $this->data['vat_rate_id'] = resolve_static(VatRate::class, 'default')?->id;
            }
        }
    }
}
