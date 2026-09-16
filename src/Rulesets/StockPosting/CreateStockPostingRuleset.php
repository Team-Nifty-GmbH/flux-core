<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Lot;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;

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
            'lot_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'product_id',
                    'table' => 'lots',
                ]),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => StockPosting::class]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_variant_parent', false)
                    ->whereDoesntHave(
                        'children',
                        fn (Builder $query) => $query->where('is_active', true)
                    ),
            ],
            'serial_number_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => SerialNumber::class]),
            ],
            'storage_area_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => StorageArea::class]),
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'warehouse_id',
                    'table' => 'storage_areas',
                ]),
            ],
            'warehouse_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Warehouse::class]),
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
