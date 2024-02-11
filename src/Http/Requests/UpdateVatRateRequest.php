<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;

class UpdateVatRateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(VatRate::class),
            ],
            'name' => 'required|string',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
            'footer_text' => 'string|nullable',
        ];
    }
}
