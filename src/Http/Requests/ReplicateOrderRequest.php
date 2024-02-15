<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\Numeric;

class ReplicateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new UpdateOrderRequest())->rules(),
            [
                'parent_id' => 'integer|nullable|exists:orders,id,deleted_at,NULL',
                'contact_id' => [
                    'required_without:address_invoice_id',
                    'integer',
                    'nullable',
                    'exists:contacts,id,deleted_at,NULL',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
                ],
                'order_positions' => 'nullable|array',
                'order_positions.*.id' => [
                    'required',
                    'integer',
                    'exists:order_positions,id,deleted_at,NULL',
                ],
                'order_positions.*.amount' => [
                    'required',
                    new Numeric(min: 0),
                ],
            ]
        );
    }
}
