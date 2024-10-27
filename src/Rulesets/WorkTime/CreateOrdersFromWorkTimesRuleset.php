<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\OrderType;
use FluxErp\Models\Product;
use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateOrdersFromWorkTimesRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTime::class;

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'order_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'round_to_minute' => 'required_if:round,ceil|required_if:round,floor|nullable|integer',
            'add_non_billable_work_times' => 'boolean',
            'round' => 'in:floor,ceil,round',
            'work_times' => 'required|array',
            'work_times.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
        ];
    }
}
