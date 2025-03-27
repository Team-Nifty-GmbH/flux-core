<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Facades\ProductType;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\ProductCrossSelling\CreateProductCrossSellingRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CreateProductRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ClientRuleset::class, 'getRules'),
            ['clients' => 'required|array'],
            resolve_static(ProductOptionRuleset::class, 'getRules'),
            resolve_static(ProductPropertyRuleset::class, 'getRules'),
            resolve_static(PriceRuleset::class, 'getRules'),
            resolve_static(BundleProductRuleset::class, 'getRules'),
            resolve_static(SupplierRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
            Arr::prependKeysWith(
                Arr::except(
                    resolve_static(CreateProductCrossSellingRuleset::class, 'getRules'),
                    'product_id'
                ),
                'product_cross_sellings.*.'
            )
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            'uuid' => 'nullable|string|uuid|unique:products,uuid',
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'vat_rate_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'purchase_unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'reference_unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],

            'product_number' => 'string|max:255|nullable|unique:products,product_number',
            'product_type' => [
                Rule::in(ProductType::all()->keys()),
                'nullable',
            ],
            'description' => 'string|nullable',
            'weight_gram' => 'numeric|nullable',
            'dimension_length_mm' => 'numeric|nullable',
            'dimension_width_mm' => 'numeric|nullable',
            'dimension_height_mm' => 'numeric|nullable',
            'selling_unit' => 'numeric|nullable',
            'basic_unit' => 'numeric|nullable',
            'time_unit_enum' => [
                'nullable',
                'required_if:is_service,true',
                Rule::enum(TimeUnitEnum::class),
            ],
            'ean' => 'string|nullable',
            'stock' => 'integer|nullable',
            'min_delivery_time' => 'integer|nullable',
            'max_delivery_time' => 'integer|nullable',
            'restock_time' => 'integer|nullable',
            'purchase_steps' => 'numeric|nullable',
            'min_purchase' => 'numeric|nullable',
            'max_purchase' => 'numeric|nullable',
            'seo_keywords' => 'string|nullable',
            'manufacturer_product_number' => 'string|max:255|nullable',
            'posting_account' => 'string|max:255|nullable',
            'warning_stock_amount' => 'numeric|nullable',

            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
            'is_bundle' => 'boolean',
            'is_service' => 'boolean',
            'is_shipping_free' => 'boolean',
            'has_serial_numbers' => 'boolean',
            'is_nos' => 'boolean',
            'is_active_export_to_web_shop' => 'boolean',
        ];
    }
}
