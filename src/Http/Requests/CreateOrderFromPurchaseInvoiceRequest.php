<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rules\ModelExists;

class CreateOrderFromPurchaseInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
            ],
        ];
    }
}
