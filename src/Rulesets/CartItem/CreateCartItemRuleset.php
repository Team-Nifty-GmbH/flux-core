<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\Cart;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
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
                'integer',
                new ModelExists(Product::class),
            ],
            'vat_rate_id' => [
                'required_without:product_id',
                'integer',
                new ModelExists(VatRate::class),
            ],
            'name' => [
                'required',
                'string',
            ],
            'amount' => [
                'required',
                'numeric',
            ],
            'price' => [
                'required',
                'numeric',
            ],
        ];
    }
}
