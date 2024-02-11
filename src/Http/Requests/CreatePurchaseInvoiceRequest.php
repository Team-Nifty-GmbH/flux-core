<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Arr;

class CreatePurchaseInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            [
                'uuid' => 'string|uuid|unique:purchase_invoices,uuid',
                'client_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(Client::class),
                ],
                'contact_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(Contact::class),
                ],
                'currency_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(Currency::class),
                ],
                'order_type_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(OrderType::class),
                ],
                'payment_type_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(PaymentType::class),
                ],
                'invoice_date' => 'nullable|date',
                'invoice_number' => 'nullable|string',
                'is_net' => 'boolean',
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
