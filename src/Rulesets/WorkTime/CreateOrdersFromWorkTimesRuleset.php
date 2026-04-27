<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
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
            'order_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'order_type_id' => [
                'required_without:order_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'tenant_id' => [
                'required_without:order_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'round' => 'in:floor,ceil,round',
            'round_to_minute' => [
                'required_if:round,ceil',
                'required_if:round,floor',
                'nullable',
                'integer',
                'min:1',
            ],
            'add_non_billable_work_times' => 'boolean',
            'work_times' => 'required|array',
            'work_times.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
        ];
    }
}
