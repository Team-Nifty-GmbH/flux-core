<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\Address\PostalAddressRuleset;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\PaymentState\PaymentState;
use Illuminate\Support\Arr;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:orders,uuid',
            'approval_user_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Order::class),
            ],
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'agent_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'contact_id' => [
                'required_without:address_invoice_id',
                'integer',
                'nullable',
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
            ],
            'contact_bank_connection_id' => [
                'integer',
                'nullable',
                new ExistsWithForeign(foreignAttribute: 'contact_id', table: 'contact_bank_connections'),
            ],
            'currency_id' => [
                'required',
                'integer',
                new ModelExists(Currency::class),
            ],
            'address_invoice_id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'address_delivery_id' => [
                'integer',
                'nullable',
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'addresses'),
            ],
            'language_id' => [
                'integer',
                'nullable',
                new ModelExists(Language::class),
            ],
            'order_type_id' => [
                'required',
                'integer',
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'order_types'),
            ],
            'price_list_id' => [
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
            ],
            'unit_price_price_list_id' => [
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
            ],
            'payment_type_id' => [
                'integer',
                'nullable',
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'payment_types'),
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

            'delivery_state' => [
                'string',
                ValidStateRule::make(DeliveryState::class),
            ],
            'payment_state' => [
                'string',
                ValidStateRule::make(PaymentState::class),
            ],

            'payment_target' => [
                'required_with:payment_discount_target',
                'required_without_all:address_invoice_id,contact_id',
                'integer',
                'min:0',
            ],
            'payment_discount_target' => 'integer|min:0|nullable|lte:payment_target',
            'payment_discount_percent' => [
                'nullable',
                new Numeric(min: 0, max: 1),
            ],
            'header_discount' => 'numeric|min:0|nullable',
            'shipping_costs_net_price' => 'numeric|nullable',
            'margin' => 'numeric|nullable',
            'number_of_packages' => 'integer|nullable',
            'payment_reminder_days_1' => 'integer|nullable|min:1',
            'payment_reminder_days_2' => 'integer|nullable|min:1',
            'payment_reminder_days_3' => 'integer|nullable|min:1',

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
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
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
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            Arr::prependKeysWith(
                resolve_static(PostalAddressRuleset::class, 'getRules'),
                'address_delivery.'
            ),
            resolve_static(AddressRuleset::class, 'getRules'),
            resolve_static(UserRuleset::class, 'getRules')
        );
    }
}
