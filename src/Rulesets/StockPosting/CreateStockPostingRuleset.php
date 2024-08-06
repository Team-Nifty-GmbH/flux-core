<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateStockPostingRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:stock_postings,uuid',
            'warehouse_id' => [
                'required',
                'integer',
                new ModelExists(Warehouse::class),
            ],
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'purchase_price' => 'required|numeric',
            'posting' => 'required|numeric',
            'description' => 'sometimes|required|string',
        ];
    }
}
