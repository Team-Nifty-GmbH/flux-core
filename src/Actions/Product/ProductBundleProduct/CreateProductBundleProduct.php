<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ProductBundleProduct\CreateProductBundleProductRuleset;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateProductBundleProduct extends FluxAction
{
    public static function models(): array
    {
        return [BundleProductProduct::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductBundleProductRuleset::class;
    }

    public function performAction(): BundleProductProduct
    {
        $productBundleProduct = app(BundleProductProduct::class, ['attributes' => $this->data]);
        $productBundleProduct->save();

        resolve_static(Product::class, 'query')
            ->whereKey($this->data['product_id'])
            ->first()
            ->update([
                'bundle_type_enum' => BundleTypeEnum::Standard,
                'is_bundle' => true,
            ]);

        return $productBundleProduct->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->addRules([
            'bundle_product_id' => Rule::unique('bundle_product_product', 'bundle_product_id')
                ->where('product_id', $this->data['product_id'] ?? 0),
        ]);
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->where('is_configurable', true)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'product_id' => [__('A configurable product cannot be a bundle or a variant.')],
            ])
                ->errorBag('createProductBundleProduct');
        }
    }
}
