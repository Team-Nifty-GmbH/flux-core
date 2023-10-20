<?php

namespace FluxErp\Http\Requests;

class CreateCommissionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'commission_rate_id' => 'integer|nullable|exists:commission_rates,id',
            'order_position_id' => 'integer|nullable|exists:order_positions,id,deleted_at,NULL',
            'commission' => 'required_without:commission_rate_id,order_position_id|numeric|min:0',
        ];
    }
}
