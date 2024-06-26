<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\CartItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCartItemRuleset extends FluxRuleset
{
    protected static ?string $model = CartItem::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CartItem::class),
            ],
            'amount' => [
                'numeric',
                'min:1',
            ],
            'price' => [
                'numeric',
            ],
        ];
    }
}
