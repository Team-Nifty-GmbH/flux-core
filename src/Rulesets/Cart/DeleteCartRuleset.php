<?php

namespace FluxErp\Rulesets\Cart;

use FluxErp\Models\Cart;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCartRuleset extends FluxRuleset
{
    protected static ?string $model = Cart::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Cart::class]),
            ],
        ];
    }
}
