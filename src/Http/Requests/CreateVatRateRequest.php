<?php

namespace FluxErp\Http\Requests;

class CreateVatRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:vat_rates,uuid',
            'name' => 'required|string',
            'footer_text' => 'string|nullable',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
        ];
    }
}
