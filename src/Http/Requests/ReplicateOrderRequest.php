<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Order;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\OrderState;
use FluxErp\States\Order\PaymentState\PaymentState;
use Illuminate\Support\Arr;
use Spatie\ModelStates\Validation\ValidStateRule;

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
