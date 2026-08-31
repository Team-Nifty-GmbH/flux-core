<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Models\Pivots\ProductSupplier;
use FluxErp\Models\Unit;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SupplierRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return array_merge(
            array_fill_keys(
                array_map(
                    fn (string $field): string => 'suppliers.*.' . $field,
                    resolve_static(ProductSupplier::class, 'pivotFields')
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
                'suppliers.*.packaging_amount' => 'numeric|nullable|min:0',
                'suppliers.*.packaging_unit_id' => [
                    'integer',
                    'nullable',
                    app(ModelExists::class, ['model' => Unit::class]),
                ],
                'suppliers.*.purchase_price' => 'numeric|nullable|min:0',
                'suppliers.*.note' => 'string|nullable',
            ]
        );
    }
}
