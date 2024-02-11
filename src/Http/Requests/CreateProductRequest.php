<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Tag;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;

class CreateProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $productCrossSellingsRules = Arr::prependKeysWith(
            Arr::except((new CreateProductCrossSellingRequest())->rules(), 'product_id'),
            'product_cross_sellings.*.'
        );

        return array_merge(
            (new Product())->hasAdditionalColumnsValidationRules(),
            $productCrossSellingsRules,
            [
                'name' => 'required|string',

                'uuid' => 'string|uuid|unique:products,uuid',
                'client_id' => [
                    'required',
                    'integer',
                    new ModelExists(Client::class),
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

                'product_number' => 'string|nullable|unique:products,product_number',
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
                'is_nos' => 'boolean',
                'is_active_export_to_web_shop' => 'boolean',

                'product_options' => 'array',
                'product_options.*' => [
                    'required',
                    'integer',
                    new ModelExists(ProductOption::class),
                ],
                'product_properties' => 'array',
                'product_properties.*.id' => [
                    'required',
                    'integer',
                    new ModelExists(ProductProperty::class),
                ],
                'product_properties.*.value' => 'required|string',

                'prices' => 'array',
                'prices.*.price_list_id' => [
                    'required',
                    'integer',
                    new ModelExists(PriceList::class),
                ],
                'prices.*.price' => 'required|numeric',

                'bundle_products' => 'required_if:is_bundle,true|array|exclude_unless:is_bundle,true',
                'bundle_products.*.id' => [
                    'required',
                    'integer',
                    new ModelExists(Product::class),
                ],
                'bundle_products.*.count' => 'required|numeric|min:0',

                'suppliers' => 'array',
                'suppliers.*.contact_id' => [
                    'required',
                    'integer',
                    new ModelExists(Contact::class),
                ],
                'suppliers.*.manufacturer_product_number' => 'string|nullable',
                'suppliers.*.purchase_price' => 'numeric|nullable|min:0',

                'categories' => 'array',
                'categories.*' => [
                    'integer',
                    (new ModelExists(Category::class))->where('model_type', Product::class),
                ],

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
