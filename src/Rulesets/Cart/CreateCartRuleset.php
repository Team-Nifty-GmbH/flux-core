<?php

namespace FluxErp\Rulesets\Cart;

use FluxErp\Models\PaymentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasCart;

class CreateCartRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'authenticatable_type' => [
                'nullable',
                'string',
                new MorphClassExists(uses: HasCart::class),
            ],
            'authenticatable_id' => [
                'nullable',
                'integer',
                new MorphExists(modelAttribute: 'authenticatable_type'),
            ],
            'payment_type_id' => [
                'integer',
                (new ModelExists(PaymentType::class))
                    ->where('is_active', true)
                    ->where('is_sales', true),
            ],
            'session_id' => 'required|string',
            'name' => 'nullable|string|max:255',
            'is_portal_public' => 'boolean',
            'is_public' => 'boolean',
            'is_watchlist' => 'boolean',
        ];
    }
}
