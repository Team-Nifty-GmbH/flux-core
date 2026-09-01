<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
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
                'suppliers.*.purchase_price' => 'numeric|nullable|min:0',
            ]
        );
    }
}
