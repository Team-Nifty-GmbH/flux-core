<?php

namespace FluxErp\Http\Requests;

class CreateCommissionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'commission_rate_id' => 'integer|nullable|exists:commission_rates,id',
            'order_position_id' => 'integer|nullable|exists:order_positions,id,deleted_at,NULL',
            'commission_rate' => 'required_without:commission_rate_id|numeric|gt:0|lt:1',
            'total_net_price' => 'required_without:order_position_id|numeric',
        ];
    }
}
