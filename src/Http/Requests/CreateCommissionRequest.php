<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\CommissionRate;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class CreateCommissionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'commission_rate_id' => [
                'integer',
                'nullable',
                new ModelExists(CommissionRate::class),
            ],
            'order_position_id' => [
                'integer',
                'nullable',
                new ModelExists(OrderPosition::class),
            ],
            'commission_rate' => 'required_without:commission_rate_id|numeric|gt:0|lt:1',
            'total_net_price' => 'required_without:order_position_id|numeric',
        ];
    }
}
