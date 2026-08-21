<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Lot;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateStockPostingRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(SerialNumberRuleset::class, 'getRules'),
            resolve_static(AddressRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:stock_postings,uuid',
            'warehouse_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
            ],
            'warehouse_bin_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
            'lot_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => StockPosting::class]),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'serial_number_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => SerialNumber::class]),
            ],
            'purchase_price' => [
                'nullable',
                new Numeric(),
            ],
            'posting' => [
                'required',
                new Numeric(),
            ],
            'description' => 'string|nullable',
        ];
    }
}
