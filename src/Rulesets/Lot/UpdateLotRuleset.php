<?php

namespace FluxErp\Rulesets\Lot;

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLotRuleset extends FluxRuleset
{
    protected static ?string $model = Lot::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
            ],
            'product_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'lot_number' => 'sometimes|required|string|max:255',
            'supplier_lot_number' => 'nullable|string|max:255',
            'produced_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'blocked_at' => 'nullable|date',
            'description' => 'nullable|string',
        ];
    }
}
