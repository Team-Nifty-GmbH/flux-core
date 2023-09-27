<?php

namespace FluxErp\Http\Requests;

class UpdateVatRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:vat_rates,id,deleted_at,NULL',
            'name' => 'required|string',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
            'footer_text' => 'string|nullable',
        ];
    }
}
