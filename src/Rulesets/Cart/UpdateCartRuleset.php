<?php

namespace FluxErp\Rulesets\Cart;

use FluxErp\Models\Cart;
use FluxErp\Models\PaymentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCartRuleset extends FluxRuleset
{
    protected static ?string $model = Cart::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Cart::class]),
            ],
            'payment_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class])
                    ->where('is_active', true)
                    ->where('is_sales', true),
            ],
            'name' => 'nullable|string|max:255',
            'is_portal_public' => 'boolean',
            'is_public' => 'boolean',
            'is_watchlist' => 'boolean',
        ];
    }
}
