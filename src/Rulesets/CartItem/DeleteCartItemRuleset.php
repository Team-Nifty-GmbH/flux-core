<?php

namespace FluxErp\Rulesets\CartItem;

use FluxErp\Models\CartItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCartItemRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = CartItem::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CartItem::class]),
            ],
        ];
    }
}
