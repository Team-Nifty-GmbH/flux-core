<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Facades\ProductType;
use FluxErp\Models\Pivots\ProductTenant;
use FluxErp\Models\Price;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Product\CreateProductRuleset;
use FluxErp\Settings\ProductSettings;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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
        $tenants = Arr::pull($this->data, 'tenants', []);

        $suppliers = Arr::pull($this->data, 'suppliers', false);
        $tags = Arr::pull($this->data, 'tags', []);

        $product = app(Product::class, ['attributes' => $this->data]);
        $product->save();

        $product->productOptions()->attach($productOptions);
        $product->ownProductProperties()->attach($productProperties);

        if ($suppliers) {
            $product->ownSuppliers()->attach($suppliers);
        }

        if ($tags) {
            $product->attachTags(resolve_static(Tag::class, 'query')->whereKey($tags)->get());
        }

        if ($tenants) {
            $product->tenants()->attach($tenants);
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

        if (! $this->getData('is_bundle')) {
            $this->data['bundle_type_enum'] = null;
        }

        $inheritanceEnabled = app(ProductSettings::class)->variant_inheritance_enabled;

        if (
            ! $inheritanceEnabled
            && ! data_get($this->data, 'prices')
            && data_get($this->data, 'parent_id')
        ) {
            $this->data['prices'] = resolve_static(Price::class, 'query')
                ->where('product_id', data_get($this->data, 'parent_id'))
                ->get()
                ->toArray();
        }

        if (! data_get($this->data, 'tenants')) {
            if (data_get($this->data, 'parent_id')) {
                $this->data['tenants'] = resolve_static(ProductTenant::class, 'query')
                    ->where('product_id', data_get($this->data, 'parent_id'))
                    ->pluck('tenant_id')
                    ->toArray();
            } else {
                $this->data['tenants'] = [
                    resolve_static(Tenant::class, 'default')?->id,
                ];
            }
        }

        if (! data_get($this->data, 'vat_rate_id')) {
            if (data_get($this->data, 'parent_id') && ! $inheritanceEnabled) {
                $this->data['vat_rate_id'] = resolve_static(Product::class, 'query')
                    ->whereKey(data_get($this->data, 'parent_id'))
                    ->value('vat_rate_id');
            } elseif (! data_get($this->data, 'parent_id')) {
                $this->data['vat_rate_id'] = resolve_static(VatRate::class, 'default')?->id;
            }
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('is_configurable') && ($this->getData('is_bundle') || $this->getData('parent_id'))) {
            throw ValidationException::withMessages([
                'is_configurable' => [__('A configurable product cannot be a bundle or a variant.')],
            ])
                ->errorBag('createProduct');
        }

        if ($this->getData('parent_id')
            && resolve_static(Product::class, 'query')
                ->whereKey($this->getData('parent_id'))
                ->where('is_configurable', true)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [__('A configurable product cannot be a bundle or a variant.')],
            ])
                ->errorBag('createProduct');
        }
    }
}
