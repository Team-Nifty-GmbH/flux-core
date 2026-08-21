<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class TransferStockRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'from_warehouse_bin_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
            'to_warehouse_bin_id' => [
                'required',
                'integer',
                'different:from_warehouse_bin_id',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
            'amount' => [
                'required',
                'gt:0',
                new Numeric(),
            ],
            'description' => 'nullable|string',
        ];
    }
}
