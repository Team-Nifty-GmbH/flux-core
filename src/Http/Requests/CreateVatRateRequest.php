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
            'name' => 'required|string',
            'rate_percentage' => 'required|numeric|lt:1|min:0',
        ];
    }
}
