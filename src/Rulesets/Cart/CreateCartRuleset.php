<?php

namespace FluxErp\Rulesets\Cart;

use FluxErp\Models\Cart;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasCart;

class CreateCartRuleset extends FluxRuleset
{
    protected static ?string $model = Cart::class;

    public function rules(): array
    {
        return [
            'authenticatable_type' => [
                'required_with:authenticatable_id',
                'nullable',
                'string',
                'max:255',
                app(MorphClassExists::class, ['uses' => HasCart::class]),
            ],
            'authenticatable_id' => [
                'required_with:authenticatable_type',
                'nullable',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'authenticatable_type']),
            ],
            'payment_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class])
                    ->where('is_active', true)
                    ->where('is_sales', true),
            ],
            'price_list_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'session_id' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'is_portal_public' => 'boolean',
            'is_public' => 'boolean',
            'is_watchlist' => 'boolean',
        ];
    }
}
