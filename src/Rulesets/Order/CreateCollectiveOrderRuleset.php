<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;

class CreateCollectiveOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'order_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class])
                    ->where('order_type_enum', OrderTypeEnum::CollectiveOrder->value)
                    ->where('is_active', true),
            ],
            'split_order_order_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class])
                    ->where('order_type_enum', OrderTypeEnum::SplitOrder->value)
                    ->where('is_active', true),
            ],
            'orders' => 'required|array',
            'orders.*' => 'array',
            'orders.*.address_invoice_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'orders.*.orders' => 'required|array',
            'orders.*.orders.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class])
                    ->whereNull('invoice_number')
                    ->whereDoesntHave('children')
                    ->whereRelation('orderPositions', 'total_net_price', '>', 0)
                    ->whereHas(
                        'orderType',
                        fn (Builder $query) => $query
                            ->where('order_type_enum', OrderTypeEnum::Order->value)
                            ->where('is_active', true)
                    ),
            ],
        ];
    }
}
