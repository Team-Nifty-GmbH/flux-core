<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\Address\PostalAddressRuleset;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\OrderState;
use FluxErp\States\Order\PaymentState\PaymentState;
use Illuminate\Support\Arr;

class UpdateOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

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

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'agent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'approval_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'contact_bank_connection_id' => [
                'integer',
                'nullable',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'contact_id',
                    'table' => 'contact_bank_connections',
                    'baseTable' => 'orders',
                ]),
            ],
            'address_invoice_id' => [
                'sometimes',
                'required',
                'integer',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'client_id',
                    'table' => 'addresses',
                    'baseTable' => 'orders',
                ]),
            ],
            'address_delivery_id' => [
                'integer',
                'nullable',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'client_id',
                    'table' => 'addresses',
                    'baseTable' => 'orders',
                ]),
            ],
            'language_id' => [
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'order_type_id' => [
                'sometimes',
                'required',
                'integer',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'client_id',
                    'table' => 'order_types',
                    'baseTable' => 'orders',
                ]),
            ],
            'price_list_id' => [
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'unit_price_price_list_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'payment_type_id' => [
                'sometimes',
                'required',
                'integer',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'client_id',
                    'table' => 'client_payment_type',
                    'column' => 'payment_type_id',
                    'baseTable' => 'orders',
                ]),
            ],
            'responsible_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'vat_rate_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => VatRate::class])
                    ->where('is_tax_exemption', true),
            ],

            'address_delivery' => [
                'array',
                'nullable',
            ],
            'address_delivery.id' => [
                'integer',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'client_id',
                    'table' => 'addresses',
                    'baseTable' => 'orders',
                ]),
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
            'payment_reminder_current_level' => 'integer|nullable|min:0',
            'payment_reminder_next_date' => 'date|nullable',

            'order_number' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                app(UniqueInFieldDependence::class, ['model' => Order::class, 'dependingField' => 'client_id']),
            ],
            'commission' => 'string|max:255|nullable',
            'header' => 'string|nullable',
            'footer' => 'string|nullable',
            'logistic_note' => 'string|nullable',
            'tracking_email' => 'email|max:255|nullable',
            'payment_texts' => 'array|nullable',

            'order_date' => 'date',
            'invoice_date' => 'date|nullable',
            'invoice_number' => [
                'exclude_if:invoice_number,null',
                'string',
                'max:255',
            ],
            'system_delivery_date' => 'required_with:system_delivery_date_end|date|nullable',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'customer_delivery_date' => 'date|nullable',
            'date_of_approval' => 'date|nullable',

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
}
