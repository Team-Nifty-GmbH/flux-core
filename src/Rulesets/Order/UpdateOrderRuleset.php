<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\Address\PostalAddressRuleset;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\OrderState;
use FluxErp\States\Order\PaymentState\PaymentState;
use Illuminate\Support\Arr;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'agent_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'approval_user_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'contact_bank_connection_id' => [
                'integer',
                'nullable',
                new ExistsWithForeign(
                    foreignAttribute: 'contact_id',
                    table: 'contact_bank_connections',
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
                new ModelExists(Language::class),
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
                new ModelExists(PriceList::class),
            ],
            'unit_price_price_list_id' => [
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
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
                new ModelExists(User::class),
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
            'payment_target' => 'sometimes|required_with:payment_discount_target|integer|min:0',
            'payment_discount_target' => 'integer|min:0|nullable|lte:payment_target',
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
            'system_delivery_date' => 'required_with:system_delivery_date_end|date|nullable',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
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
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            Arr::prependKeysWith(
                resolve_static(PostalAddressRuleset::class, 'getRules'),
                'address_delivery.'
            ),
            resolve_static(AddressRuleset::class, 'getRules'),
            resolve_static(UserRuleset::class, 'getRules')
        );
    }
}
