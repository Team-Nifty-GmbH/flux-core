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
                app(ModelExists::class, ['model' => Cart::class]),
            ],
            'product_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'vat_rate_id' => [
                'required_without:product_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'name' => [
                'required_without:product_id',
                'nullable',
                'string',
                'max:255',
            ],
            'amount' => [
                'nullable',
                app(Numeric::class, ['min' => 1]),
            ],
            'price' => [
                'required',
                app(Numeric::class),
            ],
            'order_column' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
