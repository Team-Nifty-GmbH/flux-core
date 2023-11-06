<?php

namespace FluxErp\Http\Requests;

class UpdateCommissionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:commissions,id',
            'commission' => 'required|numeric',
        ];
    }
}
