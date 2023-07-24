<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use FluxErp\Rules\ExistsWithIgnore;

class UpdateProductRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Product())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:products,id,deleted_at,NULL',
                'parent_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('products', 'id'))->whereNull('deleted_at'),
                ],
                'vat_rate_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('vat_rates', 'id'))->whereNull('deleted_at'),
                ],
                'unit_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('units', 'id'))->whereNull('deleted_at'),
                ],
                'purchase_unit_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('units', 'id'))->whereNull('deleted_at'),
                ],
                'reference_unit_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('units', 'id'))->whereNull('deleted_at'),
                ],

                'product_number' => 'string|nullable',
                'name' => 'string',
                'description' => 'string|nullable',
                'weight_gram' => 'numeric|nullable',
                'dimension_height_mm' => 'numeric|nullable',
                'dimension_width_mm' => 'numeric|nullable',
                'dimension_length_mm' => 'numeric|nullable',
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
                'is_shipping_free' => 'boolean',
                'is_required_product_serial_number' => 'boolean',
                'is_required_manufacturer_serial_number' => 'boolean',
                'is_auto_create_serial_number' => 'boolean',
                'is_product_serial_number' => 'boolean',
                'is_nos' => 'boolean',
                'is_active_export_to_web_shop' => 'boolean',

                'product_options' => 'array',
                'product_options.*' => 'required_with:product_options|integer|exists:product_options,id,deleted_at,NULL',
                'product_properties' => 'array',
                'product_properties.*.id' => [
                    'required_with:product_properties',
                    'integer',
                    'exists:product_properties,id,deleted_at,NULL',
                ],
                'product_properties.*.value' => 'required_with:product_properties|string',

                'prices' => 'array',
                'prices.*.price_list_id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
                'prices.*.price' => 'required|numeric',

                'bundle_products' => 'required_if:is_bundle,true|array|exclude_unless:is_bundle,true',
                'bundle_products.*.id' => 'required|integer|exists:products,id,deleted_at,NULL',
                'bundle_products.*.count' => 'required|numeric|min:0',

                'categories' => 'array',
                'categories.*' => 'integer|exists:' . Category::class . ',id,model_type,' . Product::class,

                'tags' => 'array',
                'tags.*' => 'string',
            ],
        );
    }
}
