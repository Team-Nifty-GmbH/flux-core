<?php

namespace FluxErp\Http\Requests;

class UpdatePurchaseInvoicePositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:purchase_invoices,id,deleted_at,NULL',
            'product_id' => 'sometimes|integer|exists:products,id,deleted_at,NULL',
            'vat_rate_id' => 'sometimes|integer|exists:vat_rates,id,deleted_at,NULL',
            'name' => 'nullable|string',
            'amount' => 'numeric',
            'unit_price' => 'numeric',
            'total_price' => 'numeric',
        ];
    }
}
