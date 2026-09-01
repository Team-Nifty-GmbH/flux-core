<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class SupplierRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return array_merge(
            array_fill_keys(
                array_map(
                    fn (string $field): string => 'suppliers.*.' . $field,
                    app(Product::class)->suppliers()->getPivotColumns()
                ),
                'nullable'
            ),
            [
                'suppliers' => 'array',
                'suppliers.*.contact_id' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => Contact::class]),
                ],
                'suppliers.*.manufacturer_product_number' => 'string|max:255|nullable',
                'suppliers.*.supplier_product_number' => 'string|max:255|nullable',
                'suppliers.*.supplier_product_name' => 'string|max:255|nullable',
                'suppliers.*.packaging_amount' => [
                    'nullable',
                    'required_with:suppliers.*.packaging_unit_id',
                    app(Numeric::class, ['min' => 0]),
                ],
                'suppliers.*.packaging_unit_id' => [
                    'nullable',
                    'required_with:suppliers.*.packaging_amount',
                    'integer',
                    app(ModelExists::class, ['model' => Unit::class]),
                ],
                'suppliers.*.purchase_price' => [
                    'nullable',
                    app(Numeric::class, ['min' => 0]),
                ],
                'suppliers.*.note' => 'string|nullable',
            ]
        );
    }
}
