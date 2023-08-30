<?php

namespace FluxErp\Http\Requests;

class CreateStockPostingRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:stock_postings,uuid',
            'warehouse_id' => 'required|integer|exists:warehouses,id,deleted_at,NULL',
            'product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'purchase_price' => 'required|numeric',
            'posting' => 'required|numeric',
            'description' => 'sometimes|required|string',
        ];
    }
}
