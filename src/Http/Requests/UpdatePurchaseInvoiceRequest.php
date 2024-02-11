<?php

namespace FluxErp\Http\Requests;

use Illuminate\Support\Arr;

class UpdatePurchaseInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge([
                'id' => 'required|integer|exists:purchase_invoices,id,deleted_at,NULL',
                'client_id' => 'nullable|integer|exists:clients,id,deleted_at,NULL',
                'contact_id' => 'nullable|integer|exists:contacts,id,deleted_at,NULL',
                'currency_id' => 'nullable|integer|exists:currencies,id,deleted_at,NULL',
                'order_type_id' => 'nullable|integer|exists:order_types,id,deleted_at,NULL',
                'payment_type_id' => 'nullable|integer|exists:payment_types,id,deleted_at,NULL',
                'invoice_date' => 'nullable|date',
                'invoice_number' => 'nullable|string',
                'total_gross' => 'nullable|numeric',
                'purchase_invoice_positions' => 'array',
            ],
            Arr::prependKeysWith(
                (new UpdatePurchaseInvoicePositionRequest())->rules(),
                'purchase_invoice_positions.*.'
            )
        );
    }
}
