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

class UpdateOrderRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Order())->hasAdditionalColumnsValidationRules(),
            Arr::prependKeysWith((new CreateAddressRequest())->postalAddressRules(), 'address_delivery.'),
            [
                'id' => 'required|integer|exists:orders,id,deleted_at,NULL',
                'agent_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
                ],
                'approval_user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
                'bank_connection_id' => [
                    'integer',
                    'nullable',
                    new ExistsWithForeign(
                        foreignAttribute: 'contact_id',
                        table: 'bank_connections',
                        baseTable: 'orders'
                    ),
                ],
                'address_invoice_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    new ExistsWithForeign(
                        foreignAttribute: 'client_id',
                        table: 'addresses',
                        baseTable: 'orders'
                    ),
                ],
                'address_delivery_id' => [
                    'integer',
                    'nullable',
                    new ExistsWithForeign(
                        foreignAttribute: 'client_id',
                        table: 'addresses',
                        baseTable: 'orders'
                    ),
                ],
                'language_id' => [
                    'integer',
                    (new ExistsWithIgnore('languages', 'id'))->whereNull('deleted_at'),
                ],
                'order_type_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    new ExistsWithForeign(
                        foreignAttribute: 'client_id',
                        table: 'order_types',
                        baseTable: 'orders'
                    ),
                ],
                'price_list_id' => [
                    'integer',
                    (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
                ],
                'unit_price_price_list_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
                ],
                'payment_type_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    new ExistsWithForeign(
                        foreignAttribute: 'client_id',
                        table: 'payment_types',
                        baseTable: 'orders'
                    ),
                ],
                'responsible_user_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
                ],

                'address_delivery' => [
                    'array',
                    'nullable',
                ],
                'address_delivery.id' => [
                    'integer',
                    new ExistsWithForeign(
                        foreignAttribute: 'client_id',
                        table: 'addresses',
                        baseTable: 'orders'
                    ),
                ],

                'state' => [
                    'string',
                    ValidStateRule::make(OrderState::class),
                ],
                'delivery_state' => [
                    'string',
                    ValidStateRule::make(DeliveryState::class),
                ],
                'payment_state' => [
                    'string',
                    ValidStateRule::make(PaymentState::class),
                ],
                'payment_target' => 'sometimes|integer|min:0',
                'payment_discount_target' => 'integer|min:0|nullable',
                'payment_discount_percent' => 'numeric|min:0|nullable',
                'header_discount' => 'numeric|min:0|nullable',
                'shipping_costs_net_price' => 'numeric|nullable',
                'margin' => 'sometimes|numeric|nullable',
                'number_of_packages' => 'sometimes|integer|nullable',
                'payment_reminder_days_1' => 'sometimes|integer|min:1',
                'payment_reminder_days_2' => 'sometimes|integer|min:1',
                'payment_reminder_days_3' => 'sometimes|integer|min:1',

                'order_number' => [
                    'sometimes',
                    'required',
                    'string',
                    new UniqueInFieldDependence(Order::class, 'client_id'),
                ],
                'commission' => 'string|nullable',
                'header' => 'sometimes|string|nullable',
                'footer' => 'sometimes|string|nullable',
                'logistic_note' => 'sometimes|string|nullable',
                'tracking_email' => 'sometimes|email|nullable',
                'payment_texts' => 'array|nullable',

                'order_date' => 'sometimes|date',
                'invoice_date' => 'sometimes|date|nullable',
                'invoice_number' => [
                    'exclude_if:invoice_number,null',
                    'string',
                ],
                'system_delivery_date' => 'sometimes|date|nullable',
                'customer_delivery_date' => 'sometimes|date|nullable',
                'date_of_approval' => 'sometimes|date|nullable',

                'has_logistic_notify_phone_number' => 'sometimes|boolean',
                'has_logistic_notify_number' => 'sometimes|boolean',
                'is_locked' => 'sometimes|boolean',
                'is_new_customer' => 'sometimes|boolean',
                'is_imported' => 'sometimes|boolean',
                'is_merge_invoice' => 'sometimes|boolean',
                'is_confirmed' => 'sometimes|boolean',
                'is_paid' => 'sometimes|boolean',
                'requires_approval' => 'sometimes|boolean',

                'addresses' => 'array',
                'addresses.*.address_id' => [
                    'integer',
                    (new ExistsWithIgnore('addresses', 'id'))->whereNull('deleted_at'),
                ],
                'addresses.*.address_type_id' => [
                    'integer',
                    'distinct',
                    (new ExistsWithIgnore('address_types', 'id'))->whereNull('deleted_at'),
                ],

                'users' => 'array',
                'users.*' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
                ],
            ],
        );
    }
}
