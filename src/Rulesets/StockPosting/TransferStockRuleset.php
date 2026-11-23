<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class TransferStockRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    public function rules(): array
    {
        return [
            'from_storage_area_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
            'lot_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'product_id',
                    'table' => 'lots',
                ]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'to_storage_area_id' => [
                'required',
                'integer',
                'different:from_storage_area_id',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
            'warehouse_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'amount' => [
                'required',
                'gt:0',
                app(Numeric::class),
            ],
            'description' => 'nullable|string',
        ];
    }
}
