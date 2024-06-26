<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\CartItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCartItemRuleset extends FluxRuleset
{
    protected static ?string $model = CartItem::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CartItem::class),
            ],
        ];
    }
}
