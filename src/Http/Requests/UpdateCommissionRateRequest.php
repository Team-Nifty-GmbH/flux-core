<?php

namespace FluxErp\Http\Requests;

class UpdateCommissionRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:commission_rates,id',
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
