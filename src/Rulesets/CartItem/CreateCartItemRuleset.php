<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\Cart;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateCartItemRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'cart_id' => [
                'required',
                'integer',
                new ModelExists(Cart::class),
            ],
            'product_id' => [
                'nullable',
                'integer',
                new ModelExists(Product::class),
            ],
            'vat_rate_id' => [
                'required_without:product_id',
                'nullable',
                'integer',
                new ModelExists(VatRate::class),
            ],
            'name' => [
                'required_without:product_id',
                'nullable',
                'string',
            ],
            'amount' => [
                'nullable',
                new Numeric(1),
            ],
            'price' => [
                'nullable',
                new Numeric(),
            ],
        ];
    }
}
