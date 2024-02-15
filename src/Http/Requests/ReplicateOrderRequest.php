<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;

class ReplicateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new UpdateOrderRequest())->rules(),
            [
                'parent_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Order::class),
                ],
                'contact_id' => [
                    'required_without:address_invoice_id',
                    'integer',
                    'nullable',
                    new ModelExists(Contact::class),
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
                ],
                'order_positions' => 'nullable|array',
                'order_positions.*.id' => [
                    'required',
                    'integer',
                    new ModelExists(OrderPosition::class),
                ],
                'order_positions.*.amount' => [
                    'required',
                    new Numeric(min: 0),
                ],
            ]
        );
    }
}
