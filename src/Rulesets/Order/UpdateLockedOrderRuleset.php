<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Arr;

class UpdateLockedOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return array_merge(
            Arr::only(
                resolve_static(UpdateOrderRuleset::class, 'getRules'),
                [
                    'state',
                    'delivery_state',
                    'payment_state',
                    'approval_user_id',
                    'responsible_user_id',
                    'payment_reminder_current_level',
                    'payment_reminder_next_date',
                    'date_of_approval',
                    'is_confirmed',
                    'requires_approval',

                    'commission',

                    'addresses',
                    'users',
                ]
            ),
            [
                'id' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => Order::class]),
                ],
            ]
        );
    }
}
