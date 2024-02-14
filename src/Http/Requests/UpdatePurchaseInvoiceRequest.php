<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Arr;

class UpdatePurchaseInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            [
                'id' => [
                    'required',
                    'integer',
                    new ModelExists(PurchaseInvoice::class),
                ],
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
                'invoice_date' => 'date',
                'invoice_number' => 'nullable|string',
                'is_net' => 'boolean',

                'purchase_invoice_positions' => 'array',
            ],
            Arr::prependKeysWith(
                (new UpdatePurchaseInvoicePositionRequest())->rules(),
                'purchase_invoice_positions.*.'
            ),
            [
                'purchase_invoice_positions.*.id' => [
                    'integer',
                    new ModelExists(PurchaseInvoicePosition::class),
                ],
            ]
        );
    }
}
