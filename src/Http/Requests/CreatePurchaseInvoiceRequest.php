<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\MediaUploadType;
use Illuminate\Support\Arr;

class CreatePurchaseInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            [
                'uuid' => 'string|uuid|unique:purchase_invoices,uuid',
                'client_id' => 'nullable|integer|exists:clients,id,deleted_at,NULL',
                'contact_id' => 'nullable|integer|exists:contacts,id,deleted_at,NULL',
                'currency_id' => 'nullable|integer|exists:currencies,id,deleted_at,NULL',
                'order_type_id' => 'nullable|integer|exists:order_types,id,deleted_at,NULL',
                'payment_type_id' => 'nullable|integer|exists:payment_types,id,deleted_at,NULL',
                'invoice_date' => 'nullable|date',
                'invoice_number' => 'nullable|string',
                'total_gross' => 'nullable|numeric',
                'media' => 'required',
                'media_type' => ['sometimes', new MediaUploadType()],
                'purchase_invoice_positions' => 'nullable|array',
                'purchase_invoice_positions.*' => 'required|array',
            ],
            Arr::prependKeysWith(
                (new CreatePurchaseInvoicePositionRequest())->rules(),
                'purchase_invoice_positions.*.'
            )
        );
    }
}
