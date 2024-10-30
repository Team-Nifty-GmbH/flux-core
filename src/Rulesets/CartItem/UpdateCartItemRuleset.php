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
                app(ModelExists::class, ['model' => CartItem::class]),
            ],
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 1]),
            ],
            'price' => [
                'sometimes',
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
