<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
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
                'nullable',
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
            'serial_number_ids' => 'nullable|array',
            'serial_number_ids.*' => [
                'required',
                'integer',
                'distinct',
                app(ModelExists::class, ['model' => SerialNumber::class]),
            ],
            'stock_posting_ids' => 'nullable|array',
            'stock_posting_ids.*' => [
                'required',
                'integer',
                'distinct',
                app(ModelExists::class, ['model' => StockPosting::class]),
            ],
            'to_storage_area_id' => [
                'nullable',
                'integer',
                'different:from_storage_area_id',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
            'to_warehouse_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
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
