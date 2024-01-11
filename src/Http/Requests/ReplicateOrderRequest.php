<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithForeign;

class ReplicateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new UpdateOrderRequest())->rules(),
            [
                'contact_id' => [
                    'required_without:address_invoice_id',
                    'integer',
                    'nullable',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
                ],
            ]
        );
    }
}
