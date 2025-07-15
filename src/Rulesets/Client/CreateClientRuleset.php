<?php

namespace FluxErp\Rulesets\Client;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateClientRuleset extends FluxRuleset
{
    protected static ?string $model = Client::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:clients,uuid',
            'country_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'commission_credit_note_order_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class])
                    ->where('order_type_enum', OrderTypeEnum::Refund)
                    ->where('is_active', true),
            ],
            'name' => 'required|max:255|string',
            'client_code' => 'required|string|max:255|unique:clients,client_code',
            'ceo' => 'string|max:255|nullable',
            'street' => 'string|max:255|nullable',
            'city' => 'string|max:255|nullable',
            'postcode' => 'string|max:255|nullable',
            'phone' => 'string|max:255|nullable',
            'fax' => 'string|max:255|nullable',
            'email' => 'email|max:255|nullable',
            'website' => 'string|max:255|nullable',
            'creditor_identifier' => 'string|max:255|nullable',
            'vat_id' => 'string|max:255|nullable',
            'sepa_text_b2c' => 'string|nullable',
            'sepa_text_b2b' => 'string|nullable',
            'opening_hours' => 'array|nullable',
            'terms_and_conditions' => 'string|nullable',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
