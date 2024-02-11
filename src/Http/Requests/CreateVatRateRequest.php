<?php

namespace FluxErp\Http\Requests;

class CreateVatRateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:vat_rates,uuid',
            'name' => 'required|string',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
            'footer_text' => 'string|nullable',
        ];
    }
}
