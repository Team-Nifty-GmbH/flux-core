<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Lot;
use FluxErp\Models\StockPosting;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateStockPostingRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => StockPosting::class]),
            ],
            'warehouse_bin_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'warehouse_id',
                    'table' => 'warehouse_bins',
                    'baseTable' => 'stock_postings',
                ]),
            ],
            'lot_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'product_id',
                    'table' => 'lots',
                    'baseTable' => 'stock_postings',
                ]),
            ],
            'remaining_stock' => [
                'sometimes',
                'required',
                new Numeric(),
            ],
            'reserved_stock' => [
                'sometimes',
                'required',
                new Numeric(),
            ],
            'description' => 'string|nullable',
        ];
    }
}
