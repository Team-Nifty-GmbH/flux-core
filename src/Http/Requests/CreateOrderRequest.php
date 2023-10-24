<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Order;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\PaymentState\PaymentState;
use Illuminate\Support\Arr;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateOrderRequest extends BaseFormRequest
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
                'uuid' => 'string|uuid|unique:orders,uuid',
                'approval_user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
                'parent_id' => 'integer|nullable|exists:orders,id,deleted_at,NULL',
                'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
                'agent_id' => 'integer|nullable|exists:users,id,deleted_at,Null',
                'contact_id' => [
                    'integer',
                    'nullable',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
                ],
                'bank_connection_id' => [
                    'integer',
                    'nullable',
                    new ExistsWithForeign(foreignAttribute: 'contact_id', table: 'bank_connections'),
                ],
                'currency_id' => 'integer|exists:currencies,id,deleted_at,NULL',
                'address_invoice_id' => [
                    'required',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'addresses'),
                ],
                'address_delivery_id' => [
                    'integer',
                    'nullable',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'addresses'),
                ],
                'language_id' => 'required|integer|exists:languages,id,deleted_at,NULL',
                'order_type_id' => [
                    'required',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'order_types'),
                ],
                'price_list_id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
                'unit_price_price_list_id' => 'integer|nullable|exists:price_lists,id,deleted_at,NULL',
                'payment_type_id' => [
                    'required',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'payment_types'),
                ],
                'responsible_user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',

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

                'delivery_state' => [
                    'string',
                    ValidStateRule::make(DeliveryState::class),
                ],
                'payment_state' => [
                    'string',
                    ValidStateRule::make(PaymentState::class),
                ],

                'payment_target' => 'required_without_all:address_invoice_id,contact_id|integer|min:0',
                'payment_discount_target' => 'integer|min:0|nullable',
                'payment_discount_percent' => 'numeric|min:0|nullable',
                'header_discount' => 'numeric|min:0|nullable',
                'shipping_costs_net_price' => 'numeric|nullable',
                'margin' => 'numeric|nullable',
                'number_of_packages' => 'integer|nullable',
                'payment_reminder_days_1' => 'required_without_all:address_invoice_id,contact_id|integer|min:1',
                'payment_reminder_days_2' => 'required_without_all:address_invoice_id,contact_id|integer|min:1',
                'payment_reminder_days_3' => 'required_without_all:address_invoice_id,contact_id|integer|min:1',

                'order_number' => 'sometimes|required|string|unique:orders',
                'commission' => 'string|nullable',
                'header' => 'string|nullable',
                'footer' => 'string|nullable',
                'logistic_note' => 'string|nullable',
                'tracking_email' => 'email|nullable',
                'payment_texts' => 'array|nullable',

                'order_date' => 'date',
                'invoice_date' => 'date|nullable',
                'invoice_number' => 'string',
                'system_delivery_date' => 'date|nullable',
                'customer_delivery_date' => 'date|nullable',
                'date_of_approval' => 'date|nullable',

                'has_logistic_notify_phone_number' => 'boolean',
                'has_logistic_notify_number' => 'boolean',
                'is_locked' => 'boolean',
                'is_new_customer' => 'boolean',
                'is_imported' => 'boolean',
                'is_merge_invoice' => 'boolean',
                'is_confirmed' => 'boolean',
                'is_paid' => 'boolean',
                'requires_approval' => 'boolean',

                'addresses' => 'array',
                'addresses.*.address_id' => [
                    'required',
                    'integer',
                    'exists:addresses,id,deleted_at,NULL',
                ],
                'addresses.*.address_type_id' => [
                    'required',
                    'integer',
                    'exists:address_types,id,deleted_at,NULL',
                ],

                'users' => 'array',
                'users.*' => 'required|integer|exists:users,id,deleted_at,NULL',
            ]
        );
    }
}
