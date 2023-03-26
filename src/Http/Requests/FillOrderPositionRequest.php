<?php

namespace FluxErp\Http\Requests;

class FillOrderPositionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id,deleted_at,NULL',
            'order_positions' => 'required|array',
            'simulate' => 'boolean',
        ];
    }
}
