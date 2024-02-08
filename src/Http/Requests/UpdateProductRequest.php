<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Tag;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Product())->hasAdditionalColumnsValidationRules(),
            [
                'id' => [
                    'required',
                    'integer',
                    new ModelExists(Product::class),
                ],
                'cover_media_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Media::class),
                ],
                'parent_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Product::class),
                ],
                'vat_rate_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(VatRate::class),
                ],
                'unit_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Unit::class),
                ],
                'purchase_unit_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Unit::class),
                ],
                'reference_unit_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Unit::class),
                ],

                'product_number' => 'string|nullable',
                'name' => 'string',
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
                    new Enum(TimeUnitEnum::class),
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
                'manufacturer_product_number' => 'string|nullable',
                'posting_account' => 'string|nullable',
                'warning_stock_amount' => 'numeric|nullable',

                'is_active' => 'boolean',
                'is_highlight' => 'boolean',
                'is_bundle' => 'boolean',
                'is_service' => 'boolean',
                'is_shipping_free' => 'boolean',
                'is_required_product_serial_number' => 'boolean',
                'is_required_manufacturer_serial_number' => 'boolean',
                'is_auto_create_serial_number' => 'boolean',
                'is_product_serial_number' => 'boolean',
                'is_nos' => 'boolean',
                'is_active_export_to_web_shop' => 'boolean',

                'product_options' => 'array',
                'product_options.*' => [
                    'required_with:product_options',
                    'integer',
                    new ModelExists(ProductOption::class),
                ],
                'product_properties' => 'array',
                'product_properties.*.id' => [
                    'required_with:product_properties',
                    'integer',
                    new ModelExists(ProductProperty::class),
                ],
                'product_properties.*.value' => 'required_with:product_properties|string',

                'prices' => 'array',
                'prices.*.price_list_id' => [
                    'required',
                    'integer',
                    new ModelExists(PriceList::class),
                ],
                'prices.*.price' => 'required|numeric',

                'bundle_products' => [
                    'exclude_if:is_bundle,false',
                    'required_if:is_bundle,true',
                    'array',
                    'exclude_unless:is_bundle,true',
                ],
                'bundle_products.*.id' => [
                    'required',
                    'integer',
                    new ModelExists(Product::class),
                ],
                'bundle_products.*.count' => 'required|numeric|min:0',

                'categories' => 'array',
                'categories.*' => [
                    'integer',
                    (new ModelExists(Category::class))->where('model_type', Product::class),
                ],

                'product_cross_sellings' => 'array',
                'product_cross_sellings.*.id' => [
                    'sometimes',
                    'required',
                    'integer',
                    new ModelExists(ProductCrossSelling::class),
                ],
                'product_cross_sellings.*.name' => 'required_without:id|string',
                'product_cross_sellings.*.is_active' => 'boolean',
                'product_cross_sellings.*.products' => 'required_without:product_cross_sellings.*.id|array',
                'product_cross_sellings.*.products.*' => [
                    'required',
                    'integer',
                    new ModelExists(Product::class),
                ],

                'suppliers' => 'array',
                'suppliers.*.contact_id' => [
                    'required',
                    'integer',
                    new ModelExists(Contact::class),
                ],
                'suppliers.*.manufacturer_product_number' => 'string|nullable',
                'suppliers.*.purchase_price' => 'numeric|nullable|min:0',

                'tags' => 'array',
                'tags.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Tag::class))->where('type', Product::class),
                ],
            ],
        );
    }
}
