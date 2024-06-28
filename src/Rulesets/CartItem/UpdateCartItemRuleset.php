<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\CartItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
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
                'sometimes',
                'required',
                new Numeric(1),
            ],
            'price' => [
                'sometimes',
                'required',
                new Numeric(),
            ],
        ];
    }
}
