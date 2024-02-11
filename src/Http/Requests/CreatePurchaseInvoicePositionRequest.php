<?php

namespace FluxErp\Http\Requests;

class CreatePurchaseInvoicePositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:purchase_invoice_positions,uuid',
            'purchase_invoice_id' => 'required|integer|exists:purchase_invoices,id,deleted_at,NULL',
            'product_id' => 'nullable|integer|exists:products,id,deleted_at,NULL',
            'vat_rate_id' => 'required|integer|exists:vat_rates,id,deleted_at,NULL',
            'name' => 'nullable|string',
            'amount' => 'required|numeric',
            'unit_price' => 'required|numeric',
            'total_price' => 'required|numeric',
        ];
    }
}
