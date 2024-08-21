<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SupplierRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'suppliers' => 'array',
            'suppliers.*.contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'suppliers.*.manufacturer_product_number' => 'string|nullable',
            'suppliers.*.purchase_price' => 'numeric|nullable|min:0',
        ];
    }
}
