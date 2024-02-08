<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\CommissionRate;
use FluxErp\Rules\ModelExists;

class UpdateCommissionRateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CommissionRate::class),
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
